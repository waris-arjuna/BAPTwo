<?php

namespace App\Http\Controllers;

use App\Services\MathCaptchaService;
use Illuminate\Http\JsonResponse;

class MathCaptchaController extends Controller
{
    /**
     * Refresh math subtraction captcha and return fresh SVG.
     */
    public function refresh(MathCaptchaService $captchaService): JsonResponse
    {
        $captcha = $captchaService->generate();

        return response()->json([
            'svg' => $captcha['svg'],
        ]);
    }
}
