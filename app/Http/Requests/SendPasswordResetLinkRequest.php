<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\Recaptcha;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Http\Requests\SendPasswordResetLinkRequest as FortifySendPasswordResetLinkRequest;

final class SendPasswordResetLinkRequest extends FortifySendPasswordResetLinkRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'g-recaptcha-response' => [
                Rule::when(config('services.recaptcha.enabled'), 'required'),
                new Recaptcha('forgot-password'),
            ],
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => Recaptcha::MESSAGE,
        ];
    }
}
