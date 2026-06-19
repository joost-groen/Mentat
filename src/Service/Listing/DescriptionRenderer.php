<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Listing;

class DescriptionRenderer
{
    public function render(array $template, array $values, string $name): string
    {
        $parts = [];
        foreach ($template['sections'] as $section) {
            switch ($section['type']) {
                case 'title':
                    $value = $this->esc($values[$section['key']] ?? '');
                    $parts[] = '<h1>' . $name . ' - ' . $value . '</h1>';
                    break;
                case 'description':
                    $value = $this->esc($values[$section['key']] ?? '');
                    $parts[] = '<p>' . $value . '</p>';
                    break;
                case 'table':
                    $parts[] = '<h3>' . $this->esc($section['heading']) . '</h3>';
                    $parts[] = '<table cellpadding="6" cellspacing="0" border="1" width="100%" style="margin-bottom: 20px;">';
                    $parts[] = '    <tbody>';
                    foreach ($section['rows'] as $row) {
                        $label = $this->esc($row['label'] ?? '');
                        $value = $this->esc($values[$row['key']] ?? '');
                        $parts[] = '        <tr>';
                        $parts[] = '            <th width="30%" valign="top" align="left">' . $label . '</th>';
                        $parts[] = '            <td width="70%" valign="top" align="left">' . $value . '</td>';
                        $parts[] = '        </tr>';
                    }
                    $parts[] = '    </tbody>';
                    $parts[] = '</table>';
                    break;
                case 'legal':
                    $value_ = $this->esc($section['content'] ?? '');
                    $parts[] = '<p>' . $value_ . '</p>';
                    break;
            }
        }
        return implode("\n", $parts);
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}