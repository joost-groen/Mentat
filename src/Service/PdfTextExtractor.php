<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service;

use Smalot\PdfParser\Parser;

class PdfTextExtractor
{
    public function __construct(private Parser $parser)
    {
    }

    public function extract(string $filePath): string
    {
        $pdf = $this->parser->parseFile($filePath);
        return $pdf->getText();
    }
}