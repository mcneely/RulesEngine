<?php

namespace Tests\Fixtures;

class SimpleTestObjectTwo
{
    private int $value = 0;
    private string $string = '';

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue(int $value): SimpleTestObjecttWo
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @param string $string
     * @return SimpleTestObjectTwo
     */
    public function setString(string $string): SimpleTestObjectTwo
    {
        $this->string = $string;
        return $this;
    }

}