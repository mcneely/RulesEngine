<?php

namespace McNeely\Rules\ExpressionProviders;

use McNeely\Rules\Traits\ExpressionLanguageAwareTrait;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

abstract class AbstractExpressionProvider implements ExpressionFunctionProviderInterface
{
    use ExpressionLanguageAwareTrait;
}