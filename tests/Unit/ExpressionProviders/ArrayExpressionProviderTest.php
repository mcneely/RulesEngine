<?php

namespace Tests\Unit\ExpressionProviders;

use McNeely\Rules\ExpressionProviders\ArrayExpressionProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ArrayExpressionProviderTest extends TestCase
{
    private ArrayExpressionProvider $SUT;
    private ExpressionLanguage $expressionLanguage;

    public function setUp(): void
    {
       $this->SUT = new ArrayExpressionProvider();
       $this->expressionLanguage = new ExpressionLanguage();
       $this->SUT->setExpressionLanguage($this->expressionLanguage);
       $this->expressionLanguage->registerProvider($this->SUT);
    }

    public function testFilter()
    {
        $facts = [
            'testArray' => [1,2,3,4,5,6,7,8,9,10]
        ];

        $result = $this->expressionLanguage->evaluate('filter(testArray, "value % 2 == 0")', $facts);
        $this->assertIsArray($result);
        $this->assertSame([2,4,6,8,10], $result);

        $facts = [
            'testArray' => ["Key1" => "Duck!","Key2" => "Duck!", "Key3" => "Duck!", "Key4" => "Goose!", "Key5" => "Duck!", "Key6" => "Duck!","Key7" => "Duck!","Key8" => "Duck!",]
        ];

        $result = $this->expressionLanguage->evaluate('filter(testArray, "value !== \'Duck!\'")', $facts);
        $this->assertIsArray($result);
        $this->assertSame(["Key4" =>"Goose!"], $result);
    }

    public function testGetFunctions()
    {
        $functions = $this->SUT->getFunctions();
        $this->assertIsArray($functions);
        foreach ($functions as $function) {
            $this->assertInstanceOf(ExpressionFunction::class, $function);
        }
    }
}
