<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\DownloadService;
use App\Services\OrderAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class DownloadController extends Controller
{
    public function __construct(
        private DownloadService $downloadService,
        private OrderAccessService $orderAccessService,
    ) {}

    public function download(Request $request, Order $order, int $product)
    {
        $product = Product::withoutGlobalScope('published')->findOrFail($product);

        if (! $this->hasAccess($request, $order)) {
            abort(401, 'Invalid or expired download link.');
        }

        if (! $this->downloadService->canDownload($order, $product)) {
            abort(403, 'You do not have access to this download.');
        }

        if ($product->isExternalFile()) {
            $externalUrl = $product->externalDownloadUrl();

            if ($externalUrl === null || ! Str::startsWith($externalUrl, ['http://', 'https://'])) {
                abort(404, 'File not found.');
            }

            return redirect()->away($externalUrl);
        }

        $file = $product->getFirstMedia('file');

        if (! $file) {
            abort(404, 'File not found.');
        }

        return $file->toResponse($request);
    }

    private function hasAccess(Request $request, Order $order): bool
    {
        $user = $request->user();

        if ($user !== null && (int) $order->user_id === (int) $user->id) {
            return true;
        }

        $token = (string) $request->query('token', '');

        if ($token === '') {
            return false;
        }

        $tokenOrder = $this->orderAccessService->resolve($token);

        return $tokenOrder !== null && $tokenOrder->is($order);
    }
}
