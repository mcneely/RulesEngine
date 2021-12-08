<?php

namespace Tests\Unit;

use ArrayObject;
use McNeely\Rules\Evaluator;
use McNeely\Rules\Rule;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class RuleTest extends TestCase
{
    private ArrayObject $facts;
    private Rule $SUT;

    public function setUp(): void
    {
        $this->facts = new ArrayObject([
            'SimpleObject'    => new class() {
                private $passed = false;

                /**
                 * @return bool
                 */
                public function hasPassed(): bool
                {
                    return $this->passed;
                }

                /**
                 * @param bool $passed
                 * @return
                 */
                public function setPassed(bool $passed): self
                {
                    $this->passed = $passed;
                    return $this;
                }

            }
        ]);

        $this->evaluator = new Evaluator($this->facts, new ExpressionLanguage(), new EventDispatcher());

       $this->SUT = new Rule(
           'FOO\BAR',
           'Foo',
           [
               'when' => '2+2==4',
               'then' => [
                   'false',
                   false,
                   'SimpleObject.setPassed(true)'
               ]
           ]
       );


    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Rule::class, $this->SUT);
    }

    public function testGetName()
    {
        $this->assertEquals('Foo', $this->SUT->getName());
    }

    public function testGetThen()
    {
        $this->assertIsArray($this->SUT->getThen());
        $this->assertEquals('SimpleObject.setPassed(true)', $this->SUT->getThen()[2]);
    }

    public function testGetWhen()
    {
        $this->assertIsArray($this->SUT->getWhen());
        $this->assertEquals('2+2==4',$this->SUT->getWhen()[0]);
    }

    public function testInNamespace()
    {
        $this->assertTrue($this->SUT->inNamespace('FOO\BAR\BAZ\TEST'));
        $this->assertTrue($this->SUT->inNamespace('FOO\BAR\BAZ'));
        $this->assertFalse($this->SUT->isPrimary());
        $this->assertTrue($this->SUT->inNamespace('FOO\BAR'));
        $this->assertTrue($this->SUT->isPrimary());
        $this->assertTrue($this->SUT->inNamespace('foo\bar'));
        $this->assertFalse($this->SUT->inNamespace('FOO'));
    }
}
