<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    config()->set('services.recaptcha.enabled', true);
    config()->set('services.recaptcha.hostname_strict', false);
    config()->set('services.recaptcha.min_score', 0.5);
});

function recaptchaFake(array $payload): void
{
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify*' => Http::response(array_merge([
            'success' => true,
            'score' => 0.9,
            'action' => 'login',
            'hostname' => 'localhost',
            'challenge_ts' => now()->toIso8601String(),
        ], $payload)),
    ]);
}

test('login with a valid recaptcha token authenticates', function () {
    $user = User::factory()->create();
    recaptchaFake(['action' => 'login']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'g-recaptcha-response' => 'valid-token',
    ])->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login is rejected when the recaptcha score is too low', function () {
    $user = User::factory()->create();
    recaptchaFake(['score' => 0.2]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'g-recaptcha-response' => 'low-score-token',
    ])->assertSessionHasErrors('g-recaptcha-response');

    $this->assertGuest();
});

test('login is rejected without a recaptcha token', function () {
    $user = User::factory()->create();
    recaptchaFake([]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('g-recaptcha-response');
});

test('login works without a token when recaptcha is disabled', function () {
    config()->set('services.recaptcha.enabled', false);
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login fails open when siteverify is unreachable and fail open is enabled', function () {
    config()->set('services.recaptcha.fail_open', true);
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify*' => Http::response('', 500),
    ]);
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'g-recaptcha-response' => 'token',
    ])->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login fails closed when siteverify is unreachable and fail open is disabled', function () {
    config()->set('services.recaptcha.fail_open', false);
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify*' => Http::response('', 500),
    ]);
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'g-recaptcha-response' => 'token',
    ])->assertSessionHasErrors('g-recaptcha-response');

    $this->assertGuest();
});

test('register with a valid recaptcha token creates the user', function () {
    recaptchaFake(['action' => 'register']);

    $this->post('/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password12345',
        'password_confirmation' => 'password12345',
        'g-recaptcha-response' => 'valid-token',
    ])->assertRedirect(route('customer.dashboard'));

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});

test('register is rejected when the recaptcha score is too low', function () {
    recaptchaFake(['score' => 0.2]);

    $this->post('/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password12345',
        'password_confirmation' => 'password12345',
        'g-recaptcha-response' => 'low-score-token',
    ])->assertSessionHasErrors('g-recaptcha-response');

    $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
});

test('forgot password is rejected when the recaptcha score is too low', function () {
    User::factory()->create(['email' => 'forgot@example.com']);
    Mail::fake();
    recaptchaFake(['score' => 0.2]);

    $this->post('/forgot-password', [
        'email' => 'forgot@example.com',
        'g-recaptcha-response' => 'low-score-token',
    ])->assertSessionHasErrors('g-recaptcha-response');
});

test('forgot password proceeds with a valid recaptcha token', function () {
    $user = User::factory()->create(['email' => 'forgot@example.com']);
    Mail::fake();
    recaptchaFake(['action' => 'forgot-password']);

    $this->post('/forgot-password', [
        'email' => 'forgot@example.com',
        'g-recaptcha-response' => 'valid-token',
    ])->assertStatus(302)->assertSessionHasNoErrors();

    expect($user->fresh()->exists)->toBeTrue();
});

test('reset password is rejected when the recaptcha score is too low', function () {
    $user = User::factory()->create(['email' => 'reset@example.com']);
    $token = Password::broker()->createToken($user);
    recaptchaFake(['score' => 0.2]);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
        'g-recaptcha-response' => 'low-score-token',
    ])->assertSessionHasErrors('g-recaptcha-response');
});

test('reset password works with a valid recaptcha token', function () {
    $user = User::factory()->create(['email' => 'reset@example.com']);
    $token = Password::broker()->createToken($user);
    recaptchaFake(['action' => 'reset-password']);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
        'g-recaptcha-response' => 'valid-token',
    ])->assertRedirect(route('login'))->assertSessionHasNoErrors();

    expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
    $this->assertGuest();
});

test('login page loads the recaptcha script when enabled', function () {
    config()->set('services.recaptcha.site_key', 'site-key-123');

    $this->get('/login')
        ->assertOk()
        ->assertSee('https://www.google.com/recaptcha/api.js?render=site-key-123', false);
});

test('login page does not load the recaptcha script when disabled', function () {
    config()->set('services.recaptcha.enabled', false);

    $this->get('/login')
        ->assertOk()
        ->assertDontSee('google.com/recaptcha');
});
