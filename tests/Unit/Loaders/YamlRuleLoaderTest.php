<?php

namespace Tests\Unit\Loaders;

use McNeely\Rules\Loaders\YamlRuleLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

class YamlRuleLoaderTest extends TestCase
{
    private YamlRuleLoader $SUT;

    public function setUp(): void
    {
        $this->SUT = new YamlRuleLoader(new FileLocator([__DIR__ . "/../../Fixtures/namespaces"]));
    }

    public function testLoad()
    {
        $res = $this->SUT->load(__DIR__ . "/../../Fixtures/namespaces/org/test/Test.rf.yml");
        $this->assertIsArray($res);
        $this->assertCount(2, $res);
    }

    public function testSupports()
    {
        $this->assertTrue($this->SUT->supports('test.rf.yml', 'rule'));
        $this->assertTrue($this->SUT->supports('test.rf.yaml', 'rule'));
        $this->assertFalse($this->SUT->supports('test.yml', 'rule'));
        $this->assertFalse($this->SUT->supports('test.rf.yml', 'rulez'));
    }
}
