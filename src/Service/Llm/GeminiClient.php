<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Llm;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiClient implements LlmClientInterface
{
    public function __construct(private SystemConfigService $systemConfig, private HttpClientInterface $httpClient)
    {
    }

    public function extract(string $pdfPath, string $prompt, array $schema): array
    {
        if(!is_file($pdfPath)) {
            throw new \InvalidArgumentException('PDF file not found: ' . $pdfPath);
        }

        

        $apiKey = $this->systemConfig->getString('JoostGroenMentat.config.apiKey');
        $model  = $this->systemConfig->getString('JoostGroenMentat.config.model');

        if($apiKey === '') {
            throw new \InvalidArgumentException('API key not found');
        }

        if($model === '') {
            throw new \InvalidArgumentException('Model not found');
        }

        $bytes = file_get_contents($pdfPath);
        if ($bytes === false) {
            throw new \RuntimeException('Failed to read PDF file: ' . $pdfPath);
        }
        $pdfData = base64_encode($bytes);

        $body = [
            'contents' => [[
                'parts' => [
                    ['inline_data' => ['mime_type' => 'application/pdf', 'data' => $pdfData]],
                    ['text' => $prompt],
                ],
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema'   => $schema,
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['x-goog-api-key' => $apiKey],
            'json'    => $body,
            'timeout'      => 30,
            'max_duration' => 120,
        ]);

        $data = $response->toArray();

        if(!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \RuntimeException('No output from Gemini');
        }

        $output = $data['candidates'][0]['content']['parts'][0]['text'];

        $result = json_decode($output, true);

        if(!is_array($result)) {
            throw new \RuntimeException('Invalid output from Gemini: ' . $output);
        }
        return $result;
    }
}