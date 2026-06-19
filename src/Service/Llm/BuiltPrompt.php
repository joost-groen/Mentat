<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Llm;

class BuiltPrompt
{
    public function __construct(
        public readonly string $prompt,
        public readonly array $schema,
    ) {
    }
}