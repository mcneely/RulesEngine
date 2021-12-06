<?php

namespace McNeely\Rules\ExpressionProviders;

use McNeely\Rules\Interfaces\ExpressionLanguageAwareInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;


class ArrayExpressionProvider extends AbstractExpressionProvider implements ExpressionLanguageAwareInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'filter',
                function (){return "";},
                function(array $_arguments, array $data, string $rule) {
                    return $this->filter($data, $rule);
                }
            )
        ];
    }

    /**
     * @param array<array-key, string|array|bool|object>  $data
     * @param string $rule
     * @return array<array-key, string|array|bool|object>
     */
    private function filter(array $data, string $rule): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if(null !== $this->expressionLanguage && $this->expressionLanguage->evaluate($rule, ['value' => $value, 'key'   => $key])) {
                if(is_numeric($key)) {
                    $result[] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }


}