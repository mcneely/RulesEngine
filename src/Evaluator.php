<?php declare(strict_types=1);

namespace McNeely\Rules;

use ArrayObject;
use McNeely\Rules\Events\RolePostApplyEvent;
use McNeely\Rules\Events\RulePostEvaluateEvent;
use McNeely\Rules\Events\RulePreApplyEvent;
use McNeely\Rules\Events\RulePreEvaluateEvent;
use McNeely\Rules\Events\ThenActionFailEvent;
use McNeely\Rules\Events\WhenFalseEvent;
use McNeely\Rules\Exceptions\RuleFailException;
use McNeely\Rules\Interfaces\EvaluatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;

class Evaluator implements EvaluatorInterface
{
    /** @var ArrayObject<string, mixed> $facts  */
    private ArrayObject $facts;
    private ExpressionLanguage $expressionLanguage;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var array<RuleFailException> $failures
     */
    public array $failures = [];

    /**
     * @param ArrayObject<string, mixed> $facts
     * @param ExpressionLanguage           $expressionLanguage
     * @param EventDispatcherInterface     $eventDispatcher
     */
    public function __construct(ArrayObject $facts, ExpressionLanguage $expressionLanguage, EventDispatcherInterface $eventDispatcher)
    {
        $this->facts              = $facts;
        $this->expressionLanguage = $expressionLanguage;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Rule $rule
     * @return bool
     */
    public function evaluateWhen(Rule $rule): bool
    {
        $this->eventDispatcher->dispatch(new RulePreEvaluateEvent($rule), RulePreEvaluateEvent::NAME);
        $result = true;
        foreach ($rule->getWhen() as $whenRule) {
            $key = null;
            if(is_array($whenRule)) {
                $key = key($whenRule);
                /** @var string|bool $whenRule */
                $whenRule = $whenRule[$key];
            }

            $evaluationResult = !is_string($whenRule) ? $whenRule : $this->expressionLanguage->evaluate($whenRule, $this->facts->getArrayCopy());
            if(is_string($key)) {
                $this->facts[$key] = $evaluationResult;
            } elseif(false === $evaluationResult) {
                $this->eventDispatcher->dispatch(new WhenFalseEvent($rule, strval($whenRule)), WhenFalseEvent::NAME);
                $result = false;
            }
        }

        $this->eventDispatcher->dispatch(new RulePreEvaluateEvent($rule), RulePostEvaluateEvent::NAME);
        return $result;
    }

    /**
     * @param Rule $rule
     * @return bool
     */
    public function applyThen(Rule $rule): bool
    {
        $this->eventDispatcher->dispatch(new RulePreApplyEvent($rule), RulePreApplyEvent::NAME);
        $result = true;
        foreach ($rule->getThen() as $thenAction) {
            try {
                if (
                    is_string($thenAction) && !$this->expressionLanguage->evaluate($thenAction, $this->facts->getArrayCopy()) ||
                    is_bool($thenAction) && !$thenAction
                ) {
                    $this->eventDispatcher->dispatch(new ThenActionFailEvent($rule, strval($thenAction)), ThenActionFailEvent::NAME);
                    $result = false;
                }
            } catch (Throwable $e) {
                $this->eventDispatcher->dispatch(new ThenActionFailEvent($rule, strval($thenAction), $e), ThenActionFailEvent::NAME);
                $result = false;
            }
        }
        $this->eventDispatcher->dispatch(new RulePreApplyEvent($rule), RolePostApplyEvent::NAME);
        return $result;
    }

    /**
     * @param Rule $rule
     * @return bool
     */
    public function handleRule(Rule $rule): bool
    {
        if($this->evaluateWhen($rule)) {
            return $this->applyThen($rule);
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed  $fact
     * @return $this
     */
    public function addFact(string $key, $fact): self
    {
        $fact = is_object($fact) ? new ExtendedObject($fact) : $fact;
        $this->facts->offsetSet($key, $fact);
        return $this;
    }
}