<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Llm;

interface LlmClientInterface
{
    /**
     * Extracts text from a PDF file using a prompt and a schema.
     *
     * @param string $pdfPath absolute path to the source PDF
     * @param string $prompt  instructions: what to extract
     * @param array  $schema  JSON schema describing the structure we want back
     * @return array the model's structured answer, decoded into a PHP array
     */
    public function extract(string $pdfPath, string $prompt, array $schema): array;
}