<?php

declare(strict_types=1);

namespace McNeely\Rules\Loaders;

use McNeely\Rules\Rule;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlRuleLoader extends FileLoader
{
    private const RULE_EXTENSION = '.rf';

    /**
     * @inheritDoc
     */
    public function load($resource, string $type = null)
    {
        return Yaml::parseFile($resource ?: '');
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, string $type = null): bool
    {
        $pathInfo = pathinfo($resource);
        return in_array($pathInfo['extension'] ?? '', ['yml', 'yaml']) &&
               self::RULE_EXTENSION === substr($pathInfo['filename'], -1 * strlen(self::RULE_EXTENSION)) &&
               Rule::TYPE === $type;
    }
}