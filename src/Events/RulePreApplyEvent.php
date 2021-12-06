<?php

namespace McNeely\Rules\Events;

class RulePreApplyEvent extends AbstractRuleApplyEvent
{
    public const NAME = parent::NAME . 'pre';
}