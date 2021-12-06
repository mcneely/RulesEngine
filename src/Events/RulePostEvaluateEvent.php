<?php

namespace McNeely\Rules\Events;

class RulePostEvaluateEvent extends AbstractRuleEvaluateEvent
{
    public const NAME = parent::NAME . 'post';
}