<?php

namespace McNeely\Rules\Interfaces;

use McNeely\Rules\Rule;

interface EvaluatorInterface
{
    /**
     * @param Rule $rule
     * @return bool
     */
    public function evaluateWhen(Rule $rule): bool;

    /**
     * @param Rule $rule
     * @return bool
     */
    public function applyThen(Rule $rule): bool;
}