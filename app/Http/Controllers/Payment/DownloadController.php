<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\DownloadService;
use Illuminate\Http\Request;

final class DownloadController extends Controller
{
    public function __construct(
        private DownloadService $downloadService,
    ) {}

    public function download(Request $request, Order $order, Product $product)
    {
        $isOwner = $request->user() && $order->user_id === $request->user()->id;

        if (! $request->hasValidSignature() && ! $isOwner) {
            abort(401, 'Invalid or expired download link.');
        }

        if (! $this->downloadService->canDownload($order, $product)) {
            abort(403, 'You do not have access to this download.');
        }

        $file = $product->getFirstMedia('file');

        if (! $file) {
            abort(404, 'File not found.');
        }

        return $file->toResponse($request);
    }
}
