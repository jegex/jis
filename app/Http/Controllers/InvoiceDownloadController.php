<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Services\InvoicePdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class InvoiceDownloadController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice): Response
    {
        abort_unless($this->canAccess($request, $invoice), 403);

        $order = $invoice->order;
        $generator = app(InvoicePdfGenerator::class);

        $media = $generator->storedPdf($order);

        if ($media === null && $order->status === OrderStatus::Paid) {
            $generator->generate($order);

            $media = $generator->storedPdf($order);
        }

        abort_if($media === null, 404, 'Invoice file not found.');

        return Storage::disk($media->disk)->response(
            $media->getPathRelativeToRoot(),
            $media->file_name,
            ['Content-Type' => 'application/pdf'],
            $request->boolean('inline') ? 'inline' : 'attachment',
        );
    }

    private function canAccess(Request $request, Invoice $invoice): bool
    {
        $user = $request->user();

        if ($user === null) {
            return false;
        }

        if ((bool) $user->is_admin) {
            return true;
        }

        return (int) $invoice->order->user_id === (int) $user->id
            && $invoice->order->status === OrderStatus::Paid;
    }
}
