<?php

namespace App\Services;

class MathCaptchaService
{
    public const SESSION_ANSWER_KEY = 'math_captcha_answer';

    public const SESSION_TIME_KEY = 'math_captcha_time';

    public const TTL_SECONDS = 300; // 5 menit

    public const WIDTH = 240;

    public const HEIGHT = 80;

    /**
     * Generate a new subtraction captcha, store the answer in session, and return the SVG and question text.
     * Constraint: A > B, result > 0.
     *
     * @return array{svg: string, question: string}
     */
    public function generate(int $min = 15, int $max = 60): array
    {
        $a = random_int($min, $max);
        $b = random_int(1, $a - 1);
        $answer = $a - $b;
        $question = "{$a} - {$b} = ?";

        session([
            self::SESSION_ANSWER_KEY => $answer,
            self::SESSION_TIME_KEY => now()->timestamp,
        ]);

        $svg = $this->renderSvg($question);

        return [
            'svg' => $svg,
            'question' => $question,
        ];
    }

    /**
     * Validate the given captcha answer against the session.
     * Removes the session value on attempt (one-time use).
     */
    public function validate(mixed $input): bool
    {
        $answer = session()->pull(self::SESSION_ANSWER_KEY);
        $time = session()->pull(self::SESSION_TIME_KEY);

        if ($answer === null || $time === null) {
            return false;
        }

        if (now()->timestamp - $time > self::TTL_SECONDS) {
            return false;
        }

        if (! is_numeric($input)) {
            return false;
        }

        return (int) $input === (int) $answer;
    }

    /**
     * Render an obfuscated, distorted 240x80 SVG with wavy filters, multiple fonts, rotation, skew, and noise lines.
     */
    protected function renderSvg(string $question): string
    {
        $width = self::WIDTH;
        $height = self::HEIGHT;
        $filterId = 'captcha-warp-'.bin2hex(random_bytes(3));

        $fonts = [
            'Georgia, serif',
            'Impact, sans-serif',
            '"Trebuchet MS", sans-serif',
            '"Courier New", monospace',
            '"Arial Black", sans-serif',
            'Verdana, sans-serif',
        ];

        $chars = mb_str_split($question);
        $currentX = 18;
        $textElements = '';

        foreach ($chars as $char) {
            if ($char === ' ') {
                $currentX += 12;

                continue;
            }

            $font = $fonts[array_rand($fonts)];
            $fontSize = random_int(28, 34);
            $rot = random_int(-11, 11);
            $skew = random_int(-6, 6);
            $y = 51 + random_int(-3, 3);
            $opacity = number_format(0.85 + (random_int(0, 15) / 100), 2, '.', '');

            $textElements .= "<text x=\"{$currentX}\" y=\"{$y}\" font-family='{$font}' font-size=\"{$fontSize}\" font-weight=\"bold\" fill=\"currentColor\" fill-opacity=\"{$opacity}\" transform=\"rotate({$rot} {$currentX} {$y}) skewX({$skew})\">{$char}</text>";

            $step = ($char === '-' || $char === '=') ? 20 : ($char === '?' ? 22 : 20);
            $currentX += $step;
        }

        // Strike-through bezier curve crossing through the numbers
        $y1 = random_int(30, 55);
        $y2 = random_int(30, 55);
        $cp1x = random_int(40, 100);
        $cp1y = random_int(15, 68);
        $cp2x = random_int(120, 200);
        $cp2y = random_int(15, 68);
        $curve1 = "<path d=\"M 6 {$y1} C {$cp1x} {$cp1y}, {$cp2x} {$cp2y}, ".($width - 6)." {$y2}\" fill=\"none\" stroke=\"currentColor\" stroke-opacity=\"0.42\" stroke-width=\"2.2\" stroke-linecap=\"round\" />";

        // Secondary wavy noise line
        $y3 = random_int(15, 65);
        $y4 = random_int(15, 65);
        $cp3x = random_int(60, 180);
        $cp3y = random_int(10, 70);
        $curve2 = "<path d=\"M 8 {$y3} Q {$cp3x} {$cp3y} ".($width - 8)." {$y4}\" fill=\"none\" stroke=\"currentColor\" stroke-opacity=\"0.30\" stroke-width=\"1.6\" stroke-linecap=\"round\" />";

        // Tertiary subtle noise line
        $y5 = random_int(20, 60);
        $y6 = random_int(20, 60);
        $cp4x = random_int(50, 190);
        $cp4y = random_int(10, 70);
        $curve3 = "<path d=\"M 10 {$y5} Q {$cp4x} {$cp4y} ".($width - 10)." {$y6}\" fill=\"none\" stroke=\"currentColor\" stroke-opacity=\"0.22\" stroke-width=\"1.2\" stroke-linecap=\"round\" />";

        // Random noise dots scattered across the canvas
        $dots = '';
        for ($i = 0; $i < 20; $i++) {
            $cx = random_int(6, $width - 6);
            $cy = random_int(6, $height - 6);
            $r = (random_int(10, 24) / 10);
            $dotOpacity = number_format(random_int(15, 38) / 100, 2, '.', '');
            $dots .= "<circle cx=\"{$cx}\" cy=\"{$cy}\" r=\"{$r}\" fill=\"currentColor\" opacity=\"{$dotOpacity}\" />";
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" class="h-full w-full select-none" style="overflow: visible;">
    <defs>
        <filter id="{$filterId}" x="-5%" y="-5%" width="110%" height="110%">
            <feTurbulence type="fractalNoise" baseFrequency="0.035" numOctaves="2" result="turbulence" />
            <feDisplacementMap in="SourceGraphic" in2="turbulence" scale="4.5" xChannelSelector="R" yChannelSelector="G" />
        </filter>
    </defs>
    <g filter="url(#{$filterId})">
        {$curve1}
        {$curve2}
        {$curve3}
        {$dots}
        {$textElements}
    </g>
</svg>
SVG;
    }
}
