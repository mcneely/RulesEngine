<?php

namespace Tests\Fixtures;

class SimpleTestObjectOne
{
    private ?int $value = 0;
    private bool $hasPassed = false;
    public int $publicValue = 0;

    /**
     * @return int
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @param int|null $value
     * @return $this
     */
    public function setValue(?int $value): SimpleTestObjectOne
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPassed(): bool
    {
        return $this->hasPassed;
    }

    /**
     * @param bool $hasPassed
     * @return SimpleTestObjectOne
     */
    public function setHasPassed(bool $hasPassed): SimpleTestObjectOne
    {
        $this->hasPassed = $hasPassed;
        return $this;
    }


}