<?php

namespace Tests\Unit;

use McNeely\Rules\Exceptions\InvalidNamespaceException;
use McNeely\Rules\RulesEngine;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use Tests\Fixtures\SimpleTestObjectOne;
use Tests\Fixtures\SimpleTestObjectTwo;

class RulesEngineTest extends TestCase
{
    private SimpleTestObjectOne $simpleTestObjectOne;
    private SimpleTestObjectTwo $simpleTestObjectTwo;
    private RulesEngine $SUT;
    private LoggerInterface $testLogger;

    public function setUp(): void
    {
       $this->SUT = new RulesEngine(__DIR__ . "/../Fixtures/namespaces");
       $this->simpleTestObjectOne = new SimpleTestObjectOne();
       $this->simpleTestObjectTwo = new SimpleTestObjectTwo();
       $this->simpleTestObjectOne->setValue(42);
       $this->simpleTestObjectTwo->setValue(5555);
       $this->SUT->addFact('SimpleObject', $this->simpleTestObjectOne);
       $this->SUT->addFact('testArray', [1,2,3,4,5,6,7,8,9,10]);
       $this->SUT->setNamespace('Org\Test');
       $this->testLogger = new TestLogger();
       $this->SUT->setLogger($this->testLogger);
    }

    public function testRunPassed(): void
    {
        $this->SUT->run();
        $this->assertTrue($this->simpleTestObjectOne->hasPassed());
    }

    public function testRunFailed(): void
    {
        $this->simpleTestObjectOne->setValue(1955);
        $this->SUT->setLogLevel(LogLevel::NOTICE);
        $this->SUT->run();

        $this->assertFalse($this->simpleTestObjectOne->hasPassed());

        $this->assertTrue($this->testLogger->hasNoticeThatContains("Failed Rule 'Simple Object Rule' in namespace: Org\Test"));
        $this->assertCount(1, $this->SUT->failedRules[0]->getExceptions());

    }

    public function testSubNamespaceRunPassed(): void
    {
        $this->SUT->addFact('SimpleObjectTwo', $this->simpleTestObjectTwo);
        $this->SUT->setNamespace('Org\Test\Finance');
        $this->SUT->run();
        $this->assertEquals("Woo!", $this->simpleTestObjectTwo->getString());
    }

    public function testFunctionsRunPassed(): void
    {
        $this->SUT->setNamespace('Org\Test\Function');
        $this->SUT->run();
        $this->assertTrue($this->simpleTestObjectOne->hasPassed());
    }

    public function testSubNamespaceParentRunFailed(): void
    {
        $this->SUT->addFact('SimpleObjectTwo', $this->simpleTestObjectTwo);
        $this->SUT->setNamespace('Org\Test\Finance');
        $this->simpleTestObjectOne->setValue(1955);
        $this->SUT->run();
        $this->assertFalse($this->simpleTestObjectOne->hasPassed());
        $this->assertEquals("Woo!", $this->simpleTestObjectTwo->getString());
        $this->assertTrue($this->testLogger->hasInfoThatContains("Failed Rule 'Finance Object Rule' in namespace: Org\Test\Finance"));

    }

    public function testBadNamespaceDirectory(): void
    {
        $this->expectException(InvalidNamespaceException::class);
        $this->expectExceptionMessage('Org\Test\Foo is not a valid namespace.');
        $this->expectExceptionCode(1);
        $this->SUT->setNamespace('Org\Test\Foo');
    }

    public function testBadNamespaceFile(): void
    {
        $this->expectException(InvalidNamespaceException::class);
        $this->expectExceptionMessage('Org\Test\Logistics is not a valid namespace.');
        $this->expectExceptionCode(1);
        $this->SUT->setNamespace('Org\Test\Logistics');
    }

}
