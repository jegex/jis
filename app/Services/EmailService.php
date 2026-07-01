<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EmailTemplateType;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class EmailService
{
    public function sendOrderConfirmation(Order $order): void
    {
        $template = EmailTemplate::where('type', EmailTemplateType::OrderConfirmation)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return;
        }

        $recipient = $order->user?->email ?? $order->guest_email;
        if (! $recipient) {
            return;
        }

        $this->send($template, $recipient, [
            'customer_name' => $order->user?->name ?? $order->guest_name ?? 'Customer',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'product_name' => $order->items->first()?->product_name ?? '',
            'total' => Str::price($order->total, $order->currency_code),
            'download_url' => $this->getDownloadUrl($order),
        ], $order);
    }

    public function sendDownloadLink(Order $order): void
    {
        $template = EmailTemplate::where('type', EmailTemplateType::DownloadLink)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return;
        }

        $recipient = $order->user?->email ?? $order->guest_email;
        if (! $recipient) {
            return;
        }

        $this->send($template, $recipient, [
            'customer_name' => $order->user?->name ?? $order->guest_name ?? 'Customer',
            'product_name' => $order->items->first()?->product_name ?? '',
            'download_url' => $this->getDownloadUrl($order),
        ], $order);
    }

    public function sendPasswordResetLink(User $user, string $resetUrl): void
    {
        $template = EmailTemplate::where('type', EmailTemplateType::PasswordReset)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return;
        }

        $this->send($template, $user->email, [
            'customer_name' => $user->name,
            'reset_url' => $resetUrl,
            'site_title' => config('app.name'),
        ], $user);
    }

    public function sendEmailVerificationLink(User $user, string $verificationUrl): void
    {
        $template = EmailTemplate::where('type', EmailTemplateType::EmailVerification)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return;
        }

        $this->send($template, $user->email, [
            'customer_name' => $user->name,
            'verification_url' => $verificationUrl,
            'site_title' => config('app.name'),
        ], $user);
    }

    public function sendNewsletter(User $user, EmailTemplate $template): void
    {
        $this->send($template, $user->email, [
            'customer_name' => $user->name,
            'site_title' => config('app.name'),
            'newsletter_content' => '',
        ], $user);
    }

    public function sendWelcome(User $user): void
    {
        $template = EmailTemplate::where('type', EmailTemplateType::Welcome)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return;
        }

        $this->send($template, $user->email, [
            'customer_name' => $user->name,
            'site_title' => config('app.name'),
        ], $user);
    }

    public function parseTemplate(string $template, array $variables): string
    {
        $search = array_map(fn ($key) => '{'.$key.'}', array_keys($variables));

        return str_replace($search, array_values($variables), $template);
    }

    private function getDownloadUrl(Order $order): string
    {
        $product = $order->items->first()?->product;
        if (! $product) {
            return '#';
        }

        $locale = $order->locale ?? $order->user?->locale ?? app()->getLocale();

        return app(DownloadService::class)->generateDownloadUrl($order, $product, $locale);
    }

    private function send(EmailTemplate $template, string $recipient, array $variables, ?Model $loggable = null): void
    {
        $locale = $loggable instanceof User
            ? $loggable->locale
            : ($loggable?->user?->locale ?? app()->getLocale());

        $subject = $this->parseTemplate($template->getTranslation('subject', $locale), $variables);
        $body = $this->parseTemplate($template->getTranslation('body', $locale), $variables);

        Mail::send('email.layout', [
            'subject' => $subject,
            'body' => $body,
        ], function ($message) use ($recipient, $subject) {
            $message->to($recipient)
                ->subject($subject);
        });
    }
}
