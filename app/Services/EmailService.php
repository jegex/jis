<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EmailTemplateType;
use App\Enums\OrderStatus;
use App\Mail\OrderConfirmationMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

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

        if ($order->status === OrderStatus::Paid && ! $order->relationLoaded('invoice') && ! $order->invoice()->exists()) {
            try {
                app(InvoicePdfGenerator::class)->generate($order);
            } catch (Throwable $exception) {
                Log::warning('Failed to resolve invoice before order confirmation email', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $locale = $order->user?->locale ?? app()->getLocale();

        $product = $order->items->first()?->product;
        $isPreorder = $product?->isPreorder() ?? false;
        $releaseDate = $product?->release_date?->translatedFormat('j F Y');

        $downloadUrl = $this->getDownloadUrl($order);
        $productName = $order->items->first()?->product_name ?? '';

        $downloadSection = $isPreorder
            ? '<p>'.__('Download will be available on :date.', ['date' => $releaseDate ?? '']).'</p>'
            : '<p><a href="'.$downloadUrl.'" target="_blank">'.__('Download Now').'</a></p>';

        $variables = [
            'customer_name' => $order->user?->name ?? $order->guest_name ?? 'Customer',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'product_name' => $productName,
            'total' => Str::price($order->total, $order->currency_code),
            'download_url' => $downloadUrl,
            'download_section' => $downloadSection,
            'invoice_number' => $order->invoice?->number ?? '-',
            'is_preorder' => $isPreorder,
            'release_date' => $releaseDate,
            'preorder_info' => $isPreorder
                ? __('This product is a preorder and will be available on :date. Download will be opened automatically.', ['date' => $releaseDate])
                : null,
        ];

        Mail::to($recipient)->send(new OrderConfirmationMail(
            recipient: $recipient,
            subjectTemplate: (string) $template->getTranslation('subject', $locale),
            bodyTemplate: (string) $template->getTranslation('body', $locale),
            invoice: $this->getInvoiceAttachment($order),
            variables: $variables,
        ));

        $this->recordEmailLog(
            $order,
            EmailTemplateType::OrderConfirmation,
            $recipient,
            $this->parseTemplate((string) $template->getTranslation('subject', $locale), $variables),
        );
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

        $locale = $order->user?->locale ?? app()->getLocale();
        $product = $order->items->first()?->product;
        $isPreorder = $product?->isPreorder() ?? false;
        $releaseDate = $product?->release_date?->translatedFormat('j F Y');
        $downloadUrl = $this->getDownloadUrl($order);

        $downloadSection = $isPreorder
            ? '<p>'.__('This is a preorder product. Download will be available on :date.', ['date' => $releaseDate ?? '']).'</p>'
            : '<p><a href="'.$downloadUrl.'" target="_blank">'.__('Download :product_name', ['product_name' => $order->items->first()?->product_name ?? '']).'</a></p>';

        $variables = [
            'customer_name' => $order->user?->name ?? $order->guest_name ?? 'Customer',
            'product_name' => $order->items->first()?->product_name ?? '',
            'download_url' => $downloadUrl,
            'download_section' => $downloadSection,
            'is_preorder' => $isPreorder,
            'preorder_info' => $isPreorder
                ? __('This product is a preorder and will be available on :date. Download will be opened automatically.', ['date' => $releaseDate])
                : null,
        ];

        $this->send($template, $recipient, $variables, $order);
    }

    public function sendPreorderRelease(Order $order): void
    {
        $template = EmailTemplate::where('type', EmailTemplateType::PreorderRelease)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            Log::warning('PreorderRelease email template not found or inactive', [
                'order_id' => $order->id,
            ]);

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

    public function sendInvitationEmail(Invitation $invitation): void
    {
        $template = EmailTemplate::where('type', EmailTemplateType::AdminInvitation)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return;
        }

        $this->send($template, $invitation->email, [
            'name' => 'Admin',
            'email' => $invitation->email,
            'role' => $invitation->role,
            'invite_link' => route('invitation.set-password', $invitation->token),
            'inviter_name' => $invitation->inviter?->name ?? 'Admin',
        ]);
    }

    public function parseTemplate(string $template, array $variables): string
    {
        $search = array_map(fn ($key) => '{'.$key.'}', array_keys($variables));

        return str_replace($search, array_values($variables), $template);
    }

    /**
     * @return array{name: string, contents: string}|null
     */
    private function getInvoiceAttachment(Order $order): ?array
    {
        if ($order->status !== OrderStatus::Paid) {
            return null;
        }

        try {
            $invoice = app(InvoicePdfGenerator::class)->generate($order);

            $media = $order->getFirstMedia(InvoicePdfGenerator::MEDIA_COLLECTION);

            if (! $media) {
                return null;
            }

            $contents = Storage::disk($media->disk)->get($media->getPathRelativeToRoot());

            return [
                'name' => $invoice->fileName(),
                'contents' => (string) $contents,
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to attach invoice PDF to order confirmation email', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function getDownloadUrl(Order $order): string
    {
        $product = $order->items->first()?->product;

        if (! $product) {
            return '#';
        }

        return app(DownloadService::class)->generateDownloadUrl($order, $product);
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

        /** @var EmailTemplateType $type */
        $type = $template->type;

        $this->recordEmailLog(
            $loggable instanceof Order ? $loggable : null,
            $type,
            $recipient,
            $subject,
        );
    }

    private function recordEmailLog(?Order $order, EmailTemplateType $type, string $recipient, string $subject): void
    {
        EmailLog::create([
            'order_id' => $order?->id,
            'type' => $type,
            'recipient' => $recipient,
            'subject' => $subject !== '' ? $subject : null,
            'sent_at' => now(),
        ]);
    }
}
