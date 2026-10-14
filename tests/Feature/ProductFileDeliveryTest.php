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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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

it('generates a direct external url and an unsigned local url when guest checkout is allowed', function () {
    config(['checkout.allow_guest' => true]);

    $currency = Currency::factory()->create();
    $order = Order::factory()->forUser()->paid()->create();
    $external = Product::factory()->external('https://cdn.example.com/book.pdf')->create(['currency_id' => $currency->id]);
    $local = Product::factory()->create(['currency_id' => $currency->id]);
    $order->items()->create(['product_id' => $external->id, 'product_name' => 'External', 'price' => 10000, 'quantity' => 1]);
    $order->items()->create(['product_id' => $local->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $service = app(DownloadService::class);

    expect($service->generateDownloadUrl($order, $external))->toBe('https://cdn.example.com/book.pdf')
        ->and($service->generateDownloadUrl($order, $local))->toContain('/download/')
        ->and($service->generateDownloadUrl($order, $local))->not->toContain('signature');
});

it('signs the local download url when guest checkout is disabled', function () {
    config(['checkout.allow_guest' => false]);

    $currency = Currency::factory()->create();
    $order = Order::factory()->forUser()->paid()->create();
    $local = Product::factory()->create(['currency_id' => $currency->id]);
    $order->items()->create(['product_id' => $local->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $url = app(DownloadService::class)->generateDownloadUrl($order, $local);

    expect($url)->toContain('signature');
});

it('serves a local file through an unsigned url when guest checkout is allowed', function () {
    config(['checkout.allow_guest' => true]);

    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Local', 'price' => 10000, 'quantity' => 1]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'fake file content');
    $product->addMedia($tempFile)->usingName('test-download.txt')->toMediaCollection('file');

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]))->assertOk();
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

    $url = URL::temporarySignedRoute('payment.download', now()->addHours(24), [
        'order' => $order->order_number,
        'product' => $product->id,
    ]);

    $this->get($url)->assertRedirect('https://cdn.example.com/book.pdf');
});

it('returns not found when an external product has no valid url', function () {
    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->create(['file_location' => FileLocation::External, 'url_external' => null]);
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $url = URL::temporarySignedRoute('payment.download', now()->addHours(24), [
        'order' => $order->order_number,
        'product' => $product->id,
    ]);

    $this->get($url)->assertNotFound();
});

it('rejects a download when the external url is not http or https', function () {
    $order = Order::factory()->forUser()->paid()->create();
    $product = Product::factory()->external('ftp://cdn.example.com/book.pdf')->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $url = URL::temporarySignedRoute('payment.download', now()->addHours(24), [
        'order' => $order->order_number,
        'product' => $product->id,
    ]);

    $this->get($url)->assertNotFound();
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

it('emails the external url directly without the signed download route', function () {
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

    expect($downloadUrl)->toBe('https://cdn.example.com/book.pdf');
});
