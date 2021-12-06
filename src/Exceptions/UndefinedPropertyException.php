<?php

namespace McNeely\Rules\Exceptions;

class UndefinedPropertyException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Undefined property: {$name}", 3);
    }
}