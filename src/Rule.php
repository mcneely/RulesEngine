<?php declare(strict_types=1);

namespace McNeely\Rules;

use ArrayObject;
use McNeely\Rules\Exceptions\RuleFailException;

class Rule
{
    public const TYPE = 'rule';
    private string $namespace;
    private string $name;
    /**
     * @var array<string|bool>
     */
    private array $when;
    /**
     * @var array<string|bool>
     */
    private array $then;
    private bool $primary = false;

    /** @var ArrayObject<array-key,RuleFailException> $exceptions */
    private ArrayObject $exceptions;
    /**
     * @param string                        $namespace
     * @param string                        $name
     * @param array<array-key,string|mixed> $rulesData
     */
    public function __construct(string $namespace, string $name, array $rulesData)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->when = $this->getArray($rulesData['when'] ?? null);
        $this->then = $this->getArray($rulesData['then'] ?? null);
        $this->exceptions = new ArrayObject();
    }

    /**
     * @param string $namespace
     * @return bool
     */
    public function inNamespace(string $namespace): bool
    {
         if(false !== stripos($namespace, $this->namespace)) {
             if(strtolower($namespace) === strtolower($this->namespace)) {
                 $this->primary = true;
             }

             return true;
         }

        return false;
    }

    /**
     * @return array<array-key, bool|string|array<string,mixed>>
     */
    public function getWhen(): array
    {
        return $this->when;
    }

    /**
     * @param mixed $value
     * @return array<string|bool>
     */
    private function getArray($value): array
    {
        $value= null === $value ? [] : $value;

        if(!is_array($value)) {
            $value = [$value];
        }

        return $value;
    }

    /**
     * @return array<string|bool>
     */
    public function getThen(): array
    {
        return $this->then;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return ArrayObject<array-key,RuleFailException>
     */
    public function getExceptions(): ArrayObject
    {
        return $this->exceptions;
    }

    public function addException(RuleFailException $ruleFailException): self
    {
        $this->exceptions->append($ruleFailException);
        return $this;
    }

}