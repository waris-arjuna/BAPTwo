<?php

use App\Rules\MathCaptcha;
use App\Services\MathCaptchaService;
use Illuminate\Support\Facades\Validator;

it('generates a valid subtraction problem with distorted SVG where result is strictly greater than 0', function () {
    $service = app(MathCaptchaService::class);

    for ($i = 0; $i < 30; $i++) {
        $result = $service->generate();

        expect($result)->toHaveKeys(['svg', 'question']);
        expect($result['svg'])
            ->toContain('<svg')
            ->toContain('</svg>')
            ->toContain('width="240"')
            ->toContain('height="80"')
            ->toContain('viewBox="0 0 240 80"')
            ->toContain('<filter')
            ->toContain('feDisplacementMap')
            ->toContain('feTurbulence');

        $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);
        $time = session(MathCaptchaService::SESSION_TIME_KEY);

        expect($answer)->toBeInt();
        expect($answer)->toBeGreaterThan(0);
        expect($time)->toBeInt();

        // Format pertanyaan: "A - B = ?"
        preg_match('/^(\d+)\s*-\s*(\d+)\s*=\s*\?$/', $result['question'], $matches);
        expect($matches)->toHaveCount(3);

        $a = (int) $matches[1];
        $b = (int) $matches[2];

        expect($a)->toBeGreaterThan($b);
        expect($a - $b)->toBe($answer);
    }
});

it('validates a correct captcha answer and flushes session (one-time use)', function () {
    $service = app(MathCaptchaService::class);
    $service->generate();

    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);

    expect($service->validate($answer))->toBeTrue();
    // One-time use: session value was pulled, subsequent check must fail
    expect($service->validate($answer))->toBeFalse();
});

it('fails validation for wrong or non-numeric answers and flushes session', function () {
    $service = app(MathCaptchaService::class);
    $service->generate();

    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);
    $wrongAnswer = $answer + 5;

    expect($service->validate($wrongAnswer))->toBeFalse();
    // Subsequent check also fails because answer was flushed
    expect($service->validate($answer))->toBeFalse();
});

it('fails validation when captcha has expired', function () {
    $service = app(MathCaptchaService::class);
    $service->generate();

    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);

    $this->travel(305)->seconds();

    expect($service->validate($answer))->toBeFalse();
});

it('passes validation rule with correct answer and fails with invalid answer', function () {
    $service = app(MathCaptchaService::class);

    // Test passing scenario
    $service->generate();
    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);

    $validator = Validator::make(
        ['captcha' => $answer],
        ['captcha' => ['required', new MathCaptcha]]
    );
    expect($validator->passes())->toBeTrue();

    // Test failing scenario
    $service->generate();
    $validatorFail = Validator::make(
        ['captcha' => 999999],
        ['captcha' => ['required', new MathCaptcha]]
    );
    expect($validatorFail->fails())->toBeTrue();
    expect($validatorFail->errors()->first('captcha'))->toContain('captcha');
});

it('refreshes captcha via json endpoint', function () {
    $response = $this->getJson(route('captcha.refresh'));

    $response->assertOk()
        ->assertJsonStructure(['svg']);

    $responseData = $response->json();
    expect($responseData['svg'])->toContain('<svg')->toContain('feDisplacementMap');
    expect(session(MathCaptchaService::SESSION_ANSWER_KEY))->toBeGreaterThan(0);
});

it('renders signin page with captcha component and distorted SVG', function () {
    $response = $this->get(route('masuk'));

    $response->assertOk();
    $response->assertSee('name="captcha"', false);
    $response->assertSee('id="captcha"', false);
    $response->assertSee('w-[240px]', false);
    $response->assertSee('h-[80px]', false);
    $response->assertSee('<svg', false);
    $response->assertSee('width="240"', false);
    $response->assertSee('height="80"', false);
    $response->assertSee('feDisplacementMap', false);
    $response->assertSee('alt="Logo BAPTwo"', false);
    $response->assertSee('hover:shadow-theme-xl', false);
});
