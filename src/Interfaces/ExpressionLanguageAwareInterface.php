<?php

namespace McNeely\Rules\Interfaces;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

interface ExpressionLanguageAwareInterface
{
    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage): void;
}