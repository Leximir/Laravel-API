<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePromptRequest;
use App\Services\OpenAIService;
use Illuminate\Support\Str;

class ImageGenerationControllerOLD extends Controller
{
    public function __construct(private OpenAIService $openAIService)
    {

    }
    public function index()
    {

    }

    public function store(GeneratePromptRequest $request)
    {
        $user = $request->user();
        $image = $request->file('image');

        $originalName = $image->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-z0-9._-]/', '_',
            pathinfo($originalName, PATHINFO_FILENAME));
        $extension = $image->getClientOriginalExtension();
        $safeFilename = $sanitizedName . '.' . Str::random(10) . '.' . $extension;

        $imagePath = $image->storeAs('uploads/images', $safeFilename, 'public');

        $generatedPrompt = $this->openAIService->generatePromptFromImage($image);

        $imageGeneration = $user->imageGenerations()->create([
            'image_path' => $imagePath,
            'generated_prompt' => $generatedPrompt,
            'original_filename' => $originalName,
            'file_size' => $image->getSize(),
            'mime_type' => $image->getMimeType()
        ]);

        return response()->json($imageGeneration, 201);
    }
}
