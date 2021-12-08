<?php

namespace McNeely\Rules\Events;

class RolePostApplyEvent extends AbstractRuleApplyEvent
{
    public const NAME = parent::NAME . 'post';
}