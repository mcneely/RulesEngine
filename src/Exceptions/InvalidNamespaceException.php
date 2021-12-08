<?php declare(strict_types=1);

namespace McNeely\Rules\Exceptions;

use Exception;

class InvalidNamespaceException extends Exception
{
    /**
     * @param string $namespace
     */
    public function __construct(string $namespace = "")
    {
        parent::__construct("$namespace is not a valid namespace.", 1);
    }
}