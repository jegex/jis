<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Payment\DownloadController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\SitemapController;
use App\Livewire\BlogList;
use App\Livewire\CheckoutForm;
use App\Livewire\CustomerDashboard;
use App\Livewire\CustomerDownloads;
use App\Livewire\CustomerProfile;
use App\Livewire\OrderDetail;
use App\Livewire\ProductDetail;
use App\Livewire\ProductList;
use Illuminate\Support\Facades\Route;
use NielsNumbers\LaravelLocalizer\Facades\Localizer;

// ──────────────────────────────────────────────
// 1. NON-LOCALIZED ROUTES
// ──────────────────────────────────────────────

Route::prefix('auth/google')->name('auth.google.')->group(function () {
    Route::get('/', [GoogleLoginController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [GoogleLoginController::class, 'callback'])->name('callback');
});

Route::get('/currency/{currency:code}', CurrencyController::class)->name('currency.switch');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ──────────────────────────────────────────────
// 2. LOCALIZED FRONTEND ROUTES
// ──────────────────────────────────────────────

Route::translate(function () {
    Route::get(Localizer::url('home'), HomeController::class)->name('home');

    Route::get(Localizer::url('products.index'), ProductList::class)->name('products.index');
    Route::get(Localizer::url('products.show'), ProductDetail::class)->name('products.show');

    Route::get(Localizer::url('checkout.create'), CheckoutForm::class)
        ->name('checkout.create')
        ->middleware(['auth', 'verified']);

    Route::get(Localizer::url('payment.success'), [PaymentController::class, 'success'])->name('payment.success');
    Route::get(Localizer::url('payment.pending'), [PaymentController::class, 'pending'])->name('payment.pending');
    Route::get(Localizer::url('payment.error'), [PaymentController::class, 'error'])->name('payment.error');
    Route::get(Localizer::url('payment.finish'), [PaymentController::class, 'finishRedirect'])->name('payment.finish');
    Route::get(Localizer::url('payment.unfinish'), [PaymentController::class, 'unfinishRedirect'])->name('payment.unfinish');

    Route::get(Localizer::url('download.product'), [DownloadController::class, 'download'])->name('payment.download');

    Route::get(Localizer::url('pages.show'), PageController::class)->name('pages.show');

    Route::get(Localizer::url('blog.index'), BlogList::class)->name('blog.index');
    Route::get(Localizer::url('blog.show'), [BlogController::class, 'show'])->name('blog.show');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get(Localizer::url('customer.dashboard'), CustomerDashboard::class)->name('customer.dashboard');
        Route::get(Localizer::url('customer.downloads'), CustomerDownloads::class)->name('customer.downloads');
        Route::get(Localizer::url('customer.orders.show'), OrderDetail::class)->name('customer.order.show');
        Route::get(Localizer::url('customer.profile'), CustomerProfile::class)->name('customer.profile');
    });
});
