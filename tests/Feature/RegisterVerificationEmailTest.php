<?php

declare(strict_types=1);

use App\Enums\EmailTemplateType;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config()->set('services.recaptcha.enabled', false);

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::EmailVerification,
        'subject' => [app()->getLocale() => 'Verify your email'],
        'body' => [app()->getLocale() => 'Click {verification_url}'],
        'is_active' => true,
    ]);
});

function failSmtpTransport(): void
{
    config()->set('mail.default', 'smtp');
    config()->set('mail.mailers.smtp.host', '127.0.0.1');
    config()->set('mail.mailers.smtp.port', 1);
    config()->set('mail.mailers.smtp.username', 'noreply@dkikonsultan.com');
    config()->set('mail.mailers.smtp.password', 'wrong-password');
}

function registerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password12345',
        'password_confirmation' => 'password12345',
    ], $overrides);
}

test('register still succeeds when the verification email cannot be sent', function () {
    config()->set('queue.default', 'database');
    failSmtpTransport();

    $this->post('/register', registerPayload())
        ->assertRedirect(route('customer.dashboard'));

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);

    $queued = DB::table('jobs')->where('payload', 'like', '%SendEmailVerification%')->count();
    expect($queued)->toBe(1);
});

test('register with a working mailer still creates the user', function () {
    $this->post('/register', registerPayload())
        ->assertRedirect(route('customer.dashboard'));

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    $this->assertDatabaseHas('email_logs', ['recipient' => 'new@example.com']);
});
