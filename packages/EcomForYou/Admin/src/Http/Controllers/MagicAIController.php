<?php

namespace EcomForYou\Admin\Http\Controllers;

use EcomForYou\Admin\Http\Requests\MagicAI\ContentGenerationRequest;
use EcomForYou\Admin\Http\Requests\MagicAI\ImageGenerationRequest;
use EcomForYou\MagicAI\Facades\MagicAI;
use EcomForYou\MagicAI\ProviderError;
use Illuminate\Http\JsonResponse;

class MagicAIController extends Controller
{
    /**
     * Generate text content from a prompt.
     */
    public function content(ContentGenerationRequest $request): JsonResponse
    {
        if (
            ! core()->getConfigData('magic_ai.general.settings.enabled')
            || ! core()->getConfigData('magic_ai.admin_features.text_generation.enabled')
        ) {
            return new JsonResponse(['message' => trans('admin::app.errors.401.title')], 403);
        }

        try {
            return new JsonResponse([
                'content' => MagicAI::generateContent(
                    $request->input('prompt'),
                    $request->input('model'),
                ),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => ProviderError::message($e)], 500);
        }
    }

    /**
     * Generate images from a prompt.
     */
    public function image(ImageGenerationRequest $request): JsonResponse
    {
        if (
            ! core()->getConfigData('magic_ai.general.settings.enabled')
            || ! core()->getConfigData('magic_ai.admin_features.image_generation.enabled')
        ) {
            return new JsonResponse(['message' => trans('admin::app.errors.401.title')], 403);
        }

        try {
            return new JsonResponse([
                'images' => MagicAI::generateImage(
                    $request->input('prompt'),
                    $request->only(['n', 'size', 'quality']),
                    $request->input('model'),
                ),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => ProviderError::message($e)], 500);
        }
    }
}
