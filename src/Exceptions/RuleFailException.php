<?php

namespace McNeely\Rules\Exceptions;

use Throwable;

class RuleFailException extends \Exception
{
    /**
     * @param string          $rule
     * @param Throwable|null $previous
     */
    public function __construct($rule, ?Throwable $previous = null)
    {
        parent::__construct($rule, 2, $previous);
    }

}