<?php

namespace McNeely\Rules\Events;

class RulePreEvaluateEvent extends AbstractRuleEvaluateEvent
{
    public const NAME = parent::NAME . 'pre';
}