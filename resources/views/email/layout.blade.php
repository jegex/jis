<div style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f5; min-width: 100%;">
        <tr>
            <td align="center" style="padding: 40px 20px;">

                <table role="presentation" class="email-container" width="500" cellpadding="0" cellspacing="0" style="max-width: 500px; width: 100%; border-radius: 8px; white-space: nowrap;">

                    {{-- Header --}}
                    <tr>
                        <td class="email-header-padding" style="background-color: #000080; padding: 30px 40px; text-align: center;">
                            <img
                                class="logo-img"
                                src="{{ url('logo/logo_dark.png') }}"
                                alt="{{ config('app.name') }}"
                                width="126"
                                style="height: 32px; width: auto; border: 0; outline: none; display: inline-block;"
                            />
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td class="email-padding" style="background-color: #ffffff; padding: 40px; font-size: 16px; line-height: 1.6; color: #374151;">
                            {!! $body !!}
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size: 13px; line-height: 1.5; color: #94a3b8;">
                                        <p style="margin: 0 0 4px;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                                        <p style="margin: 0;">
                                            {{ url('/') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                {{-- Footer note --}}
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">
                    <tr>
                        <td style="padding: 16px 20px 0; text-align: center; font-size: 12px; line-height: 1.4; color: #94a3b8;">
                            {{ __('If you did not request this email, you can safely ignore it.') }}
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</div>
