<?php

namespace McNeely\Rules\Events;

use McNeely\Rules\Rule;
use throwable;

class WhenFalseEvent extends AbstractRuleFailedEvent
{
    public const TYPE = 'whenRule';
    public const NAME = parent::NAME . '.' . self::TYPE;

}