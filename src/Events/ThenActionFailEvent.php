<?php

namespace McNeely\Rules\Events;

class ThenActionFailEvent extends AbstractRuleFailedEvent
{
    public const TYPE = 'thenAction';
    public const NAME = parent::NAME . '.' . self::TYPE;
}