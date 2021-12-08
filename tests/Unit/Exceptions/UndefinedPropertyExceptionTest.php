<?php

namespace Tests\Unit\Exceptions;

use McNeely\Rules\Exceptions\UndefinedPropertyException;
use PHPUnit\Framework\TestCase;

class UndefinedPropertyExceptionTest extends TestCase
{
    private UndefinedPropertyException $SUT;

    public function setUp(): void
    {
        $this->SUT = new UndefinedPropertyException("Foo");
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(UndefinedPropertyException::class, $this->SUT);
        $this->assertEquals(3, $this->SUT->getCode());
        $this->assertEquals('Undefined property: Foo', $this->SUT->getMessage());
    }
}
