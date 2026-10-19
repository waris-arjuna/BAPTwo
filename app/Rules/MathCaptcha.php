<?php

namespace App\Rules;

use App\Services\MathCaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class MathCaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = app(MathCaptchaService::class);

        if (! $service->validate($value)) {
            $fail(__('Jawaban verifikasi captcha salah atau telah kedaluwarsa.'));
        }
    }
}
