<?php

declare(strict_types=1);

use App\Enums\EmailTemplateType;
use App\Enums\FileLocation;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Mail\OrderConfirmationMail;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DownloadService;
use App\Services\EmailService;
use App\Services\OrderAccessService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

function grantProductPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user->givePermissionTo($permissions);
}

it('defaults a new product to a local file location', function () {
    $product = Product::factory()->create();

    expect($product->file_location)->toBe(FileLocation::Local)
        ->and($product->isExternalFile())->toBeFalse()
        ->and($product->externalDownloadUrl())->toBeNull();
});

it('routes every product through the download endpoint regardless of file location', function () {
    $currency = Currency::factory()->create();
    $order = Order::factory()->forUser()->paid()->create();
    $external = Product::factory()->external('https://cdn.example.com/book.pdf')->create(['currency_id' => $currency->id]);
    $local = Product::factory()->create(['currency_id' => $currency->id]);
    $order->items()->create(['product_id' => $external->id, 'product_name' => 'External', 'price' => 10000, 'quantity' => 1]);
    $order->items()->create(['product_id' => $local->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $service = app(DownloadService::class);

    expect($service->generateDownloadUrl($order, $external))->toContain('/download/')
        ->and($service->generateDownloadUrl($order, $external))->not->toBe('https://cdn.example.com/book.pdf')
        ->and($service->generateDownloadUrl($order, $local))->toContain('/download/')
        ->and($service->generateDownloadUrl($order, $local))->not->toContain('signature');
});

it('embeds the access token in the download url when provided', function () {
    $currency = Currency::factory()->create();
    $order = Order::factory()->forUser()->paid()->create();
    $local = Product::factory()->create(['currency_id' => $currency->id]);
    $order->items()->create(['product_id' => $local->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $token = app(DownloadService::class)->issueAccessToken($order);
    $url = app(DownloadService::class)->generateDownloadUrl($order, $local, $token);

    expect($url)->toContain('token='.urlencode($token))
        ->and($url)->not->toContain('signature');
});

it('serves a local file through a token url without authentication', function () {
    config(['checkout.allow_guest' => true]);

    $order = Order::factory()->paid()->create();
    $product = Product::factory()->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'fake file content');
    $product->addMedia($tempFile)->usingName('test-download.txt')->toMediaCollection('file');

    $token = app(OrderAccessService::class)->issue($order);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
        'token' => $token,
    ]))->assertOk();
});

it('rejects an unsigned local download even when guest checkout is allowed', function () {
    config(['checkout.allow_guest' => true]);

    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]))->assertStatus(401);
});

it('rejects an unsigned local download when guest checkout is disabled', function () {
    config(['checkout.allow_guest' => false]);

    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]))->assertStatus(401);
});

it('redirects to the external url when the product points to an external file', function () {
    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->external('https://cdn.example.com/book.pdf')->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $token = app(OrderAccessService::class)->issue($order);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
        'token' => $token,
    ]))->assertRedirect('https://cdn.example.com/book.pdf');
});

it('rejects an external download without a token', function () {
    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->external('https://cdn.example.com/book.pdf')->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]))->assertStatus(401);
});

it('returns not found when an external product has no valid url', function () {
    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->create(['file_location' => FileLocation::External, 'url_external' => null]);
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $token = app(OrderAccessService::class)->issue($order);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
        'token' => $token,
    ]))->assertNotFound();
});

it('rejects a download when the external url is not http or https', function () {
    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->external('ftp://cdn.example.com/book.pdf')->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $token = app(OrderAccessService::class)->issue($order);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
        'token' => $token,
    ]))->assertNotFound();
});

it('stores an external file location from the product form', function () {
    Currency::factory()->create();

    $admin = User::factory()->admin()->create();
    grantProductPermissions($admin, ['ViewAny:Product', 'Create:Product']);

    Livewire::actingAs($admin)
        ->test(CreateProduct::class)
        ->fillForm([
            'title' => 'External Product',
            'slug' => 'external-product',
            'price' => 100000,
            'file_location' => FileLocation::External->value,
            'url_external' => 'https://cdn.example.com/book.pdf',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::withoutGlobalScope('published')->where('slug->en', 'external-product')->first();

    expect($product)->not->toBeNull()
        ->and($product->file_location)->toBe(FileLocation::External)
        ->and($product->url_external)->toBe('https://cdn.example.com/book.pdf');
});

it('requires the external url when the file location is external', function () {
    Currency::factory()->create();

    $admin = User::factory()->admin()->create();
    grantProductPermissions($admin, ['ViewAny:Product', 'Create:Product']);

    Livewire::actingAs($admin)
        ->test(CreateProduct::class)
        ->fillForm([
            'title' => 'Missing Url Product',
            'slug' => 'missing-url-product',
            'price' => 100000,
            'file_location' => FileLocation::External->value,
            'url_external' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['url_external']);
});

it('emails a protected download route for an external product', function () {
    Storage::fake('local');
    Mail::fake();

    $currency = Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::OrderConfirmation,
        'subject' => ['id' => 'Konfirmasi', 'en' => 'Confirmation'],
        'body' => ['id' => '{download_section}', 'en' => '{download_section}'],
        'is_active' => true,
    ]);

    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->external('https://cdn.example.com/book.pdf')->create([
        'currency_id' => $currency->id,
    ]);
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Book', 'price' => 100000, 'quantity' => 1]);

    app(EmailService::class)->sendOrderConfirmation($order);

    Mail::assertSent(OrderConfirmationMail::class);

    $downloadUrl = Mail::sent(OrderConfirmationMail::class)->first()->variables['download_url'];

    expect($downloadUrl)->toContain('/download/')
        ->and($downloadUrl)->toContain('token=')
        ->and($downloadUrl)->not->toContain('cdn.example.com');

    $this->get($downloadUrl)->assertRedirect('https://cdn.example.com/book.pdf');
});
