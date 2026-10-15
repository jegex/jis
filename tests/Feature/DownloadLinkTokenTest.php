<?php

declare(strict_types=1);

use App\Enums\EmailTemplateType;
use App\Mail\OrderConfirmationMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Services\EmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function tokenFromUrl(string $value): string
{
    if (preg_match('/token=([A-Za-z0-9]+)/', $value, $matches) === 1) {
        return $matches[1];
    }

    parse_str((string) parse_url($value, PHP_URL_QUERY), $query);

    return (string) ($query['token'] ?? '');
}

function paidGuestOrderWithLocalProduct(): array
{
    $order = Order::factory()->paid()->create([
        'guest_email' => 'guest@example.com',
        'locale' => 'id',
    ]);

    $product = Product::factory()->create();

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => 'E-book',
        'price' => 100000,
        'quantity' => 1,
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'fake file content');
    $product->addMedia($tempFile)->usingName('ebook.pdf')->toMediaCollection('file');

    return [$order, $product];
}

it('emails a signed-in-free download link carrying a valid access token', function () {
    Storage::fake('local');
    Mail::fake();

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::OrderConfirmation,
        'subject' => ['id' => 'Konfirmasi', 'en' => 'Confirmation'],
        'body' => ['id' => '{download_section}', 'en' => '{download_section}'],
        'is_active' => true,
    ]);

    [$order, $product] = paidGuestOrderWithLocalProduct();

    app(EmailService::class)->sendOrderConfirmation($order);

    $url = Mail::sent(OrderConfirmationMail::class)->first()->variables['download_url'];

    expect($url)->not->toContain('signature')
        ->and(tokenFromUrl($url))->not->toBe('');

    $this->get($url)->assertOk();

    expect($order->fresh()->access_token_hash)->not->toBeNull();
});

it('rotates the access token on every download link resend', function () {
    Storage::fake('local');
    config(['mail.default' => 'array']);

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::OrderConfirmation,
        'subject' => ['id' => 'Konfirmasi', 'en' => 'Confirmation'],
        'body' => ['id' => '{download_section}', 'en' => '{download_section}'],
        'is_active' => true,
    ]);

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::DownloadLink,
        'subject' => ['id' => 'Unduhan', 'en' => 'Download'],
        'body' => ['id' => '{download_section}', 'en' => '{download_section}'],
        'is_active' => true,
    ]);

    [$order] = paidGuestOrderWithLocalProduct();

    $service = app(EmailService::class);
    $service->sendOrderConfirmation($order);
    $firstToken = tokenFromUrl((string) Mail::getSymfonyTransport()->messages()->last()->getOriginalMessage()->getHtmlBody());

    Mail::getSymfonyTransport()->flush();
    $service->sendDownloadLink($order->fresh());
    $secondToken = tokenFromUrl((string) Mail::getSymfonyTransport()->messages()->last()->getOriginalMessage()->getHtmlBody());

    expect($secondToken)->not->toBe('')
        ->and($secondToken)->not->toBe($firstToken);

    $productId = $order->items()->first()->product_id;

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $productId,
        'token' => $firstToken,
    ]))->assertStatus(401);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $productId,
        'token' => $secondToken,
    ]))->assertOk();
});
