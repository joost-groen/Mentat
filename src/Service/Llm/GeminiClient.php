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
        // First pass: just read config so we can confirm the wiring works.
        $apiKey = $this->systemConfig->getString('JoostGroenMentat.config.apiKey');
        $model  = $this->systemConfig->getString('JoostGroenMentat.config.model');
        $pdfData = base64_encode(file_get_contents($pdfPath));

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
        ]);

        $data = $response->toArray();
        $output = $data['candidates'][0]['content']['parts'][0]['text'];
        return json_decode($output, true);
    }
}