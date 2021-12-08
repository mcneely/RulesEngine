<?php

namespace McNeely\Rules\Traits;

use Psr\Log\LogLevel;

trait LogLevelAwareTrait
{
    protected string $logLevel = LogLevel::INFO;

    /**
     * @param string $logLevel
     * @return self
     */
    public function setLogLevel(string $logLevel): self
    {
        $this->logLevel = $logLevel;
        return $this;
    }
}