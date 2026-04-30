<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiDescriptionService
{
    /**
     * @return array{short: string, long: string}
     */
    public function generate(string $name, string $type, string $provider, ?string $hint = null): array
    {
        return match ($provider) {
            'claude' => $this->generateWithClaude($name, $type, $hint),
            default  => $this->generateWithOpenAI($name, $type, $hint),
        };
    }

    public function availableProviders(): array
    {
        $options = [];

        if (filled(config('services.openai.key'))) {
            $options['openai'] = 'OpenAI (GPT-4o Mini)';
        }

        if (filled(config('services.anthropic.key'))) {
            $options['claude'] = 'Claude (Anthropic)';
        }

        return $options;
    }

    /** @return array{short: string, long: string} */
    private function generateWithOpenAI(string $name, string $type, ?string $hint): array
    {
        $key = config('services.openai.key');

        if (blank($key)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $response = Http::withToken($key)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'           => 'gpt-4o-mini',
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'system', 'content' => 'You are a professional marketing copywriter. Always respond with valid JSON.'],
                    ['role' => 'user', 'content' => $this->buildPrompt($name, $type, $hint)],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI request failed: '.$response->body());
        }

        $content = json_decode($response->json('choices.0.message.content'), true);

        return [
            'short' => $content['short'] ?? '',
            'long'  => $content['long'] ?? '',
        ];
    }

    /** @return array{short: string, long: string} */
    private function generateWithClaude(string $name, string $type, ?string $hint): array
    {
        $key = config('services.anthropic.key');

        if (blank($key)) {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

        $response = Http::withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => '2023-06-01',
        ])
            ->timeout(30)
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1024,
                'messages'   => [
                    ['role' => 'user', 'content' => $this->buildPrompt($name, $type, $hint)],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Anthropic request failed: '.$response->body());
        }

        $text = $response->json('content.0.text');
        $content = json_decode($text, true);

        return [
            'short' => $content['short'] ?? '',
            'long'  => $content['long'] ?? '',
        ];
    }

    private function buildPrompt(string $name, string $type, ?string $hint): string
    {
        $hint = filled($hint) ? " Additional context: {$hint}." : '';

        return <<<PROMPT
Write compelling marketing copy for a {$type} called "{$name}".{$hint}

Return ONLY a valid JSON object with exactly two keys:
- "short": a punchy one-sentence description (max 160 characters, no HTML)
- "long": an engaging description of 2–3 paragraphs using <p> tags, highlighting key features and benefits

Example format:
{"short": "...", "long": "<p>...</p><p>...</p>"}
PROMPT;
    }
}
