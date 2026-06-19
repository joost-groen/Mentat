<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Llm;

class ValidationResult
{
    public function __construct(
        public readonly array $values,      // the data Gemini returned
        public readonly array $missing,     // keys the schema expected but Gemini omitted
        public readonly array $emptyFields, // keys present but blank ("not found in PDF")
    ) {
    }

    public function isComplete(): bool
    {
        return $this->missing === [] && $this->emptyFields === [];
    }
}