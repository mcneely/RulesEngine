<?php

namespace McNeely\Rules\Events;

use McNeely\Rules\Rule;
use Symfony\Contracts\EventDispatcher\Event;
use throwable;

abstract class AbstractRuleFailedEvent extends Event
{
    public const NAME = 'rule.failed';
    protected Rule $rule;
    protected string $ruleString;
    protected ?throwable $throwable;

    public function __construct(Rule $rule, string $ruleString, throwable $throwable = null)
    {
        $this->rule = $rule;
        $this->throwable = $throwable;
        $this->ruleString = $ruleString;
    }

    /**
     * @return Rule
     */
    public function getRule(): Rule
    {
        return $this->rule;
    }

    /**
     * @return throwable|null
     */
    public function getThrowable(): ?throwable
    {
        return $this->throwable;
    }

    /**
     * @return string
     */
    public function getRuleString(): string
    {
        return $this->ruleString;
    }

}