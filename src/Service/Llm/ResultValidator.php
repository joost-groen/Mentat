<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Llm;

class ResultValidator
{
    public function validate(array $schema, array $result): ValidationResult
    {
        $expectedKeys = array_keys($schema['properties']); 
        $missing = [];
        $empty   = [];

        foreach ($expectedKeys as $key) {
            if (!array_key_exists($key, $result)) {
                $missing[] = $key;
                continue;
            }
            if ($result[$key] === '') {
                $empty[] = $key;
            }
        }

        return new ValidationResult($result, $missing, $empty);
    }
}