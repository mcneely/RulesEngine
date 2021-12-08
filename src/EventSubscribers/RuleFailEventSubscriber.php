<?php

namespace McNeely\Rules\EventSubscribers;

use McNeely\Rules\Events\AbstractRuleFailedEvent;
use McNeely\Rules\Events\ThenActionFailEvent;
use McNeely\Rules\Events\WhenFalseEvent;
use McNeely\Rules\Exceptions\RuleFailException;
use McNeely\Rules\RulesEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RuleFailEventSubscriber implements EventSubscriberInterface
{
    private RulesEngine $rulesEngine;

    public function __construct(RulesEngine $rulesEngine)
    {
        $this->rulesEngine = $rulesEngine;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WhenFalseEvent::NAME      => 'onRuleFailEvent',
            ThenActionFailEvent::NAME => 'onRuleFailEvent',
        ];
    }

    public function onRuleFailEvent(AbstractRuleFailedEvent $event): void
    {   $tt = $event::NAME;
        $rule = $event->getRule();
        $throwable = $event->getThrowable();
        $rule->addException(new RuleFailException($event->getRuleString(), $throwable));

        $this->rulesEngine->getLogger()->log(
            $this->rulesEngine->getLogLevel(),
            sprintf(
                "Event: %s, Failed Rule '%s' in namespace: %s for (%s)",
                $event::NAME,
                $rule->getName(),
                $rule->getNamespace(),
                $event->getRuleString()
            ),
            (null !== $throwable) ? $throwable->getTrace() : []
        );
    }

}