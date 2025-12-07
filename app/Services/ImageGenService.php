<?php

namespace App\Services;

use GuzzleHttp\Client;

class ImageGenService
{
    private $client;

    // Pollinations.ai is a great free fallback that requires no auth
    private $usePollinations = true;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Generates an image URL for a given panel description.
     *
     * @param string $description The visual description of the panel.
     * @return string The URL of the generated image.
     */
    public function generateImage($description)
    {
        // 1. Clean up description for the prompt
        $prompt = strip_tags($description);
        $prompt = substr($prompt, 0, 500); // Limit length

        // Manga Style Modifier
        $prompt .= ", manga style, black and white comic, high quality, anime art style";
        $encodedPrompt = urlencode($prompt);

        if ($this->usePollinations) {
            // Pollinations.ai API: https://image.pollinations.ai/prompt/{prompt}
            // We append a random seed to prevent caching of identical prompts if retrying
            $seed = rand(1000, 9999);
            $url = "https://image.pollinations.ai/prompt/{$encodedPrompt}?nolog=true&seed={$seed}&width=768&height=512&model=flux";

            // For the PoC, we can just return this URL directly as the browser can load it.
            // However, it's better to fetch and save it locally if we want persistence.
            // For now, let's return the URL to keep it fast and simple.
            return $url;
        }

        // Future: Add HuggingFace logic here if a token is provided
        return '';
    }
}
