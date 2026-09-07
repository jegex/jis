<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class Recaptcha implements ValidationRule
{
    public const MESSAGE = 'Kami mendeteksi aktivitas mencurigakan. Coba lagi.';

    public function __construct(private readonly string $action) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('services.recaptcha.enabled')) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $this->fail($fail);

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if ($response->failed()) {
                $this->log('siteverify request failed', ['status' => $response->status()]);

                if (! config('services.recaptcha.fail_open')) {
                    $this->fail($fail);
                }

                return;
            }

            $data = $response->json();
            $score = (float) ($data['score'] ?? 0);

            $valid = ($data['success'] ?? false) === true
                && ($data['action'] ?? '') === $this->action
                && $score >= (float) config('services.recaptcha.min_score');

            if (config('services.recaptcha.hostname_strict')
                && ($data['hostname'] ?? null) !== request()->getHost()) {
                $valid = false;
            }

            if (! $valid) {
                $this->log('recaptcha verification failed', $data);
                $this->fail($fail);
            }
        } catch (Throwable $e) {
            $this->log('recaptcha verification errored', ['exception' => $e->getMessage()]);

            if (! config('services.recaptcha.fail_open')) {
                $this->fail($fail);
            }
        }
    }

    private function fail(Closure $fail): void
    {
        $fail(self::MESSAGE);
    }

    private function log(string $context, array $details = []): void
    {
        Log::warning("[Recaptcha] {$context}", [
            'action' => $this->action,
            'ip' => request()->ip(),
            'target' => request()->input('email') ?: request()->input('name'),
            ...$details,
        ]);
    }
}
