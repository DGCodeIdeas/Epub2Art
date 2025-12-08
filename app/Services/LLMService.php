<?php

namespace App\Services;

use GuzzleHttp\Client;

class LLMService
{
    private $apiKey;
    private $client;
    // OpenRouter Endpoint
    private $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    // We can use a free or cheap model that is good at instruction following.
    // 'google/gemini-2.0-flash-exp:free' or 'meta-llama/llama-3-8b-instruct:free' are options if available.
    // For stability with the paid key, we can use 'google/gemini-flash-1.5' or 'openai/gpt-3.5-turbo' etc.
    // List of models to try in order of preference/reliability
    private $models = [
        'cognitivecomputations/dolphin-mistral-24b-venice-edition:free', // User preferred free model
        'google/gemini-1.5-flash',
        'google/gemini-flash-1.5',
        'meta-llama/llama-3-8b-instruct:free',
    ];

    public function __construct()
    {
        // Use the OPENROUTER_API_KEY from env
        $this->apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
        $this->client = new Client([
            'timeout'  => 60, // Increase timeout for AI models
        ]);
    }

    /**
     * Converts novel text into a manga script (JSON format).
     *
     * @param string $text The chunk of novel text.
     * @return array The parsed script items.
     */
    public function generateScript($text)
    {
        if (empty($this->apiKey)) {
            throw new \Exception("OpenRouter API Key not set.");
        }

        $systemPrompt = "You are a professional manga editor and scriptwriter.
        Convert the provided novel text into a manga script.
        Break the scene into panels.
        For each panel, provide:
        1. 'panel_number': The sequence number.
        2. 'description': A detailed visual description of the scene for an artist (characters, setting, action, camera angle). Keep it concise but descriptive.
        3. 'dialogue': The dialogue spoken in that panel (if any), format as 'Character: Line'. If no dialogue, use empty string.

        Output valid JSON only. The format should be a list of objects:
        [
            {\"panel_number\": 1, \"description\": \"...\", \"dialogue\": \"...\"},
            ...
        ]

        Do not include markdown formatting (like ```json). Just the raw JSON string.";

        try {
            $errors = [];

            foreach ($this->models as $model) {
                try {
                    $response = $this->client->post($this->baseUrl, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->apiKey,
                            'Content-Type' => 'application/json',
                            'HTTP-Referer' => 'http://localhost:8000', // Required by OpenRouter
                            'X-Title' => 'Epub2Art', // Required by OpenRouter
                        ],
                        'json' => [
                            'model' => $model,
                            'messages' => [
                                ['role' => 'system', 'content' => $systemPrompt],
                                ['role' => 'user', 'content' => "Novel Text:\n" . substr($text, 0, 8000)]
                            ]
                        ]
                    ]);

                    $body = json_decode($response->getBody(), true);

                    // OpenRouter/OpenAI format: choices[0].message.content
                    $generatedText = $body['choices'][0]['message']['content'] ?? '';

                    // If we got here, success!
                    break;

                } catch (\Exception $e) {
                    $errors[] = "$model: " . $e->getMessage();
                    // Continue to next model
                    continue;
                }
            }

            if (!isset($generatedText)) {
                throw new \Exception("All models failed. Details: " . implode(" | ", $errors));
            }

            // Cleanup: remove Markdown code blocks
            $generatedText = str_replace('```json', '', $generatedText);
            $generatedText = str_replace('```', '', $generatedText);
            $generatedText = trim($generatedText);

            $script = json_decode($generatedText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    [
                        'panel_number' => 1,
                        'description' => 'Error parsing JSON from AI.',
                        'dialogue' => 'Raw Output: ' . substr($generatedText, 0, 200) . '...'
                    ]
                ];
            }

            return $script;

        } catch (\Exception $e) {
            error_log("OpenRouter API Error: " . $e->getMessage());
            // Fallback Mock Mode
             return [
                [
                    'panel_number' => 1,
                    'description' => 'Error contacting AI Provider.',
                    'dialogue' => 'Error: ' . $e->getMessage()
                ]
            ];
        }
    }
}
