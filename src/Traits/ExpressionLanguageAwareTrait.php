<?php

namespace McNeely\Rules\Traits;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

trait ExpressionLanguageAwareTrait
{
    protected ?ExpressionLanguage $expressionLanguage = null;

    /**
     * @param ExpressionLanguage $expressionLanguage
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }
}