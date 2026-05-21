<?php

namespace App\Services;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class HuggingFaceService
{
    private string $apiKey;
    private string $baseUrl = 'https://router.huggingface.co/hf-inference/models';

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.key');
    }

    public function generatePromptFromImage(UploadedFile $image): string
    {
        $imageData = base64_encode(file_get_contents($image->getPathname()));
        $mimeType = $image->getMimeType();

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/Salesforce/blip-image-captioning-large", [
                'inputs' => 'data:' . $mimeType . ';base64,' . $imageData,
            ]);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        $result = $response->json();

        return $result[0]['generated_text'] ?? 'A detailed image';
    }

    public function generateImageFromPrompt(string $prompt): string
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/black-forest-labs/FLUX.1-schnell", [
                'inputs' => $prompt,
            ]);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return base64_encode($response->body());
    }
}
