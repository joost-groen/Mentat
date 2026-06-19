<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Llm;

class PromptBuilder
{
    public function build(array $template): BuiltPrompt
    {
        $properties = [];   // schema fields:  key => ['type' => 'string']
        $lines      = [];   // human field list for the prompt text

        foreach ($template['sections'] as $section) {
            switch ($section['type']) {
                case 'title':
                    $properties[$section['key']] = ['type' => 'string'];
                    $lines[] = '- ' . $section['key'] . ': ' . $section['instruction'];
                    break;
                case 'description':
                    $properties[$section['key']] = ['type' => 'string'];
                    $lines[] = '- ' . $section['key'] . ': ' . $section['instruction'];
                    break;
                case 'table':
                    foreach ($section['rows'] as $row) {
                        $properties[$row['key']] = ['type' => 'string'];
                        $lines[] = '- ' . $row['key'] . ': ' . $row['label']
                            . ' (table: ' . $section['heading'] . ')';
                    }
                    break;
                case 'legal':
                    break;
            }
        }

        $schema = [
            'type'       => 'object',
            'properties' => $properties,
        ];

        $prompt = "Extract the following fields from the attached spec-sheet PDF. "
            . "If a value is not present in the document, return an empty string — "
            . "do not guess or invent values.\n\nFields:\n"
            . implode("\n", $lines);

        return new BuiltPrompt($prompt, $schema);
    }
}