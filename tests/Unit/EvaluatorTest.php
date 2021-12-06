<?php

namespace Tests\Unit;

use _PHPStan_76800bfb5\Nette\Neon\Exception;
use ArrayObject;
use Generator;
use McNeely\Rules\Evaluator;
use McNeely\Rules\Events\AbstractRuleApplyEvent;
use McNeely\Rules\Events\AbstractRuleEvaluateEvent;
use McNeely\Rules\Events\RolePostApplyEvent;
use McNeely\Rules\Events\RulePostEvaluateEvent;
use McNeely\Rules\Events\RulePreApplyEvent;
use McNeely\Rules\Events\RulePreEvaluateEvent;
use McNeely\Rules\Exceptions\RuleFailException;
use McNeely\Rules\ExpressionProviders\AbstractExpressionProvider;
use McNeely\Rules\Rule;
use McNeely\Rules\RulesEngine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Tests\Fixtures\SimpleTestObjectOne;
use Tests\Fixtures\SimpleTestObjectTwo;

class EvaluatorTest extends TestCase
{
    private SimpleTestObjectOne $simpleTestObjectOne;
    private SimpleTestObjectTwo $simpleTestObjectTwo;
    private ArrayObject $facts;
    private Evaluator $SUT;
    private EventDispatcher $eventDispatcher;
    private ExpressionLanguage $expressionLanguage;
    private RulesEngine $rulesEngine;

    public function setUp(): void
    {
        $this->simpleTestObjectOne = new SimpleTestObjectOne();
        $this->simpleTestObjectTwo = new SimpleTestObjectTwo();
        $this->simpleTestObjectOne->setValue(42);
        $this->simpleTestObjectTwo->setValue(5555);

        $this->facts = new ArrayObject([
            'SimpleObject'    => $this->simpleTestObjectOne,
            'SimpleObjectTwo' => $this->simpleTestObjectTwo,
            'testArray' => [1,2,3,4,5,6,7,8,9,10]
        ]);

        $this->expressionLanguage = new ExpressionLanguage();
        $this->eventDispatcher = new EventDispatcher();

        $this->rulesEngine = new class(
            __DIR__ . "/../Fixtures/namespaces",
            $this->eventDispatcher
        ) extends RulesEngine {
            public function __construct(string $basePath, EventDispatcherInterface $eventDispatcher = null)
            {
                $this->providerList[] = new class() implements ExpressionFunctionProviderInterface {

                    public function getFunctions(): array
                    {
                        return [];
                    }

                    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage
                    ): self {

                        throw new \Exception("Invalid Call to function.");
                    }
                };
                parent::__construct($basePath, $eventDispatcher);
            }

            public function loadProviders(ExpressionLanguage $expressionLanguage, EventDispatcherInterface $eventDispatcher): RulesEngine
            {
                return parent::loadProviders($expressionLanguage, $eventDispatcher);
            }
        };

        $this->rulesEngine->loadProviders($this->expressionLanguage, $this->eventDispatcher);

        $this->SUT = new Evaluator($this->facts, $this->expressionLanguage, $this->eventDispatcher);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Evaluator::class, new Evaluator($this->facts, $this->expressionLanguage, new EventDispatcher()));
    }

    public function testEvaluateWhen()
    {
        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'when' => []
            ]
        );

        $preEvaluate = false;
        $postEvaluate = false;
        $this->eventDispatcher->addListener(RulePreEvaluateEvent::NAME, function (AbstractRuleEvaluateEvent $event) use (&$preEvaluate, $rule) {
            $preEvaluate = $event->getRule() === $rule;
        });

        $this->eventDispatcher->addListener(RulePostEvaluateEvent::NAME, function (AbstractRuleEvaluateEvent $event) use (&$postEvaluate, $rule) {
            $postEvaluate= $event->getRule() === $rule;
        });

        $this->assertTrue($this->SUT->evaluateWhen($rule));
        $this->assertTrue($postEvaluate);
        $this->assertTrue($preEvaluate);

        $whenRules = [
            'SimpleObject.getValue() == 42',
            'true',
            true
        ];

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'when' =>$whenRules
            ]
        );

        $this->assertTrue($this->SUT->evaluateWhen($rule));

        $whenRules[] = 'SimpleObject.getValue() == 58';
        $whenRules[] = 'false';
        $whenRules[] = false;

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'when' =>$whenRules
            ]
        );

        $this->assertFalse($this->SUT->evaluateWhen($rule));
        $this->assertCount(3, $rule->getExceptions());

    }



    public function testApplyThen()
    {
        $thenRules = [
            'SimpleObject.setHasPassed(true)',
            'true',
            true
        ];

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'then' =>$thenRules
            ]
        );

        $preApply = false;
        $postApply = false;
        $this->eventDispatcher->addListener(RulePreApplyEvent::NAME, function (AbstractRuleApplyEvent $event) use (&$preApply, $rule) {
            $preApply = $event->getRule() === $rule;
        });

        $this->eventDispatcher->addListener(RolePostApplyEvent::NAME, function (AbstractRuleApplyEvent $event) use (&$postApply, $rule) {
            $postApply= $event->getRule() === $rule;
        });

        $this->assertTrue($this->SUT->applyThen($rule));

        $this->assertTrue($preApply);
        $this->assertTrue($postApply);

        $this->assertTrue($this->simpleTestObjectOne->hasPassed());

        $thenRules[] = 'SimpleObject.invalidFunction()';

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'then' =>$thenRules
            ]
        );

        $this->assertFalse($this->SUT->applyThen($rule));
        $this->assertCount(1, $rule->getExceptions());

        $thenRules[] = 'false';
        $thenRules[] = false;

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'then' =>$thenRules
            ]
        );

        $this->assertFalse($this->SUT->applyThen($rule));
        $this->assertCount(3, $rule->getExceptions());
    }

    public function testHandleRule()
    {

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'when' => [
                    '2+2==4'
                ],
                'then' => [
                    'SimpleObject.setHasPassed(true)'
                ]
            ]
        );

        $this->assertTrue($this->SUT->handleRule($rule));

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'when' => [
                    false,
                    false
                ],
                'then' => [
                    'SimpleObject.setPassed(true)'
                ]
            ]
        );

        $this->assertFalse($this->SUT->handleRule($rule));
        $this->assertFalse($this->SUT->handleRule($rule));
        $this->assertCount(4, $rule->getExceptions());

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'when' => [
                    '2+2==4'
                ],
                'then' => [
                    false
                ]
            ]
        );

        $this->assertFalse($this->SUT->handleRule($rule));
        $this->assertInstanceOf(RuleFailException::class, $rule->getExceptions()[0]);
        $this->assertEquals(2, $rule->getExceptions()[0]->getCode());
        $this->assertCount(1, $rule->getExceptions());

        $rule = new Rule(
            'FOO\BAR',
            'Foo',
            [
                'when' => [
                    'filter(testArray, "value % 2 == 0") == [2,4,6,8,10]'
                ],
                'then' => [
                    true
                ]
            ]
        );

       $this->assertTrue($this->SUT->handleRule($rule));
    }
}
