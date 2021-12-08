<?php

namespace Tests\Unit;

use McNeely\Rules\Exceptions\UndefinedPropertyException;
use McNeely\Rules\ExtendedObject;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\SimpleTestObjectOne;
use Tests\Fixtures\SimpleTestObjectTwo;

class ExtendedObjectTest extends TestCase
{
    private SimpleTestObjectOne $simpleTestObjectOne;
    private ExtendedObject $SUT;

    public function setUp(): void
    {
        $this->simpleTestObjectOne = new SimpleTestObjectOne();
        $this->SUT = new ExtendedObject($this->simpleTestObjectOne);
    }

    public function testIsInstanceOf()
    {
        $this->assertTrue($this->SUT->isInstanceOf(SimpleTestObjectOne::class));
        $this->assertFalse($this->SUT->isInstanceOf(SimpleTestObjectTwo::class));
    }

    public function testGetObject()
    {
        $this->assertEquals($this->simpleTestObjectOne, $this->SUT->getObject());
    }

    public function testSetGet()
    {
        $this->SUT->publicValue = 1955;
        $this->SUT->value = 42;
        $this->assertEquals(1955, $this->SUT->publicValue);
        $this->assertEquals(42, $this->SUT->value);
    }

    public function testIsset()
    {
        $this->simpleTestObjectOne->setValue(null);
        $this->assertFalse(isset($this->SUT->value));
        $this->SUT->setValue(42);
        $this->assertTrue(isset($this->SUT->value));
        $this->assertFalse(isset($this->SUT->nonExistantValue));
    }


    public function testSetGetException()
    {
        $this->expectException(UndefinedPropertyException::class);
        $this->expectExceptionCode(3);

        $this->SUT->getInvalidFunction();
    }
}
