<?php declare(strict_types=1);

namespace McNeely\Rules;

use ArrayObject;
use Generator;
use McNeely\Rules\EventSubscribers\RuleFailEventSubscriber;
use McNeely\Rules\Exceptions\InvalidNamespaceException;
use McNeely\Rules\ExpressionProviders\AbstractExpressionProvider;
use McNeely\Rules\ExpressionProviders\ArrayExpressionProvider;
use McNeely\Rules\Interfaces\ExpressionLanguageAwareInterface;
use McNeely\Rules\Loaders\YamlRuleLoader;
use McNeely\Rules\Traits\LogLevelAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SplFileInfo;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class RulesEngine implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LogLevelAwareTrait;

    /** @var ArrayObject<int,Rule> $ruleset */
    private ArrayObject $ruleset;

    /** @var Loader $loader  */
    private LoaderInterface $loader;
    private string $basePath;
    protected Evaluator $evaluator;

    /** @var array<Rule> $failedRules */
    public array $failedRules = [];
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @var ArrayObject<string, mixed>
     */
    protected ArrayObject $facts;

    /**
     * @var array|AbstractExpressionProvider[]
     */
    protected array $providerList = [];

    public function __construct(string $basePath, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->basePath = realpath($basePath) ?: "" ;
        $resolver = new LoaderResolver([new YamlRuleLoader(new FileLocator())]);
        $this->loader   = new DelegatingLoader($resolver);
        $this->ruleset = new ArrayObject();
        $this->facts = new ArrayObject();
        $this->logger = new NullLogger();
        $expressionLanguage = new ExpressionLanguage();

        $eventDispatcher = (null ===$eventDispatcher) ? new EventDispatcher() : $eventDispatcher;
        $eventSubscriber = new RuleFailEventSubscriber($this);
        $eventDispatcher->addSubscriber($eventSubscriber);

        $this->providerList[] = new ArrayExpressionProvider();

        $this->loadProviders($expressionLanguage, $eventDispatcher);

        $this->evaluator = new Evaluator($this->facts, $expressionLanguage, $eventDispatcher);
    }


    protected function loadProviders(ExpressionLanguage $expressionLanguage, EventDispatcherInterface $eventDispatcher): self
    {
        foreach ($this->providerList as $provider) {
            if($provider instanceof ExpressionLanguageAwareInterface) {
                $provider->setExpressionLanguage($expressionLanguage);
            }

            $expressionLanguage->registerProvider($provider);
        }

        return $this;
    }


    public function run(): self
    {
        foreach ($this->ruleset as $rule) {
            /** @var Rule $rule */
            if (!$this->evaluator->handleRule($rule)) {
                $this->failedRules[] = $rule;
            }
        }

        return $this;
    }

    /**
     * @param string $namespace
     * @return $this
     * @throws InvalidNamespaceException
     */
    public function setNamespace(string $namespace): self
    {
        $hasPrimary = false;
        foreach ($this->importNamespaceRules($this->basePath, $namespace) as $rule) {
            $this->ruleset->append($rule);
            if($rule->isPrimary()) {
                $hasPrimary = true;
            }
        }

        if(!$hasPrimary || !is_dir($this->basePath. '/' . str_replace("\\", "/", strtolower($namespace)))) {
            throw new InvalidNamespaceException($namespace);
        }

        return $this;
    }


    /**
     * @param string $basePath
     * @param string $namespace
     * @return Generator<Rule>
     */
    private function importNamespaceRules(string $basePath, string $namespace): Generator
    {
//        $fileGlob = new ArrayObject(new GlobResource($basePath, "$namespace.*", true));
//        if(count($fileGlob) === 1 && is_file($fileGlob->offsetGet(0))) {
//            yield from $this->buildRules($this->loader->import($fileGlob->offsetGet(0), Rule::TYPE), $namespace);
//        }

        foreach (new GlobResource($basePath, "/*", true) as $fileInfo)
        {
            /** @var SplFileInfo $fileInfo */
            yield from $this->buildRules($this->loader->import($fileInfo->getPathname(), Rule::TYPE), $namespace);
        }
    }

    /**
     * @param array<string,mixed> $yamlPayload
     * @param string              $namespace
     * @return Generator<Rule>
     */
    private function buildRules(array $yamlPayload, string $namespace): Generator
    {
        foreach ($yamlPayload['rules'] as $name => $payloadRule) {
            $rule =  new Rule($yamlPayload['namespace'], $name, $payloadRule);
            if($rule->inNamespace($namespace)) {
                yield $rule;
            }
        }
    }

    /**
     * @return string
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string $key
     * @param mixed  $fact
     * @return $this
     */
    public function addFact(string $key, $fact): self
    {
       $this->evaluator->addFact($key, $fact);
        return $this;
    }
}