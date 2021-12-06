<?php

namespace McNeely\Rules\Events;

use McNeely\Rules\Rule;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractRuleEvaluateEvent extends Event
{
    public const NAME = 'rule.evaluate';
    private Rule $rule;

    public function __construct(Rule $rule) {
        $this->rule = $rule;
    }

    /**
     * @return Rule
     */
    public function getRule(): Rule
    {
        return $this->rule;
    }

}