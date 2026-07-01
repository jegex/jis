<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendOrderEmail;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class PaymentController extends Controller
{
    public function success()
    {
        return view('payment.success');
    }

    public function pending()
    {
        return view('payment.pending');
    }

    public function error()
    {
        return view('payment.error');
    }

    public function callback(Request $request)
    {
        return $this->handleCallback($request);
    }

    public function recurringCallback(Request $request)
    {
        return $this->handleCallback($request);
    }

    public function payAccountCallback(Request $request)
    {
        return $this->handleCallback($request);
    }

    public function finishRedirect(Request $request)
    {
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');

        if ($orderId && $transactionId) {
            $internalId = (int) explode('-', str_replace('ORDER-', '', $orderId))[0];
            $order = Order::find($internalId);

            if ($order && $order->status === OrderStatus::AwaitingPayment) {
                try {
                    $statusDto = app('payment')->checkStatus($transactionId);

                    if (in_array($statusDto->status, ['settlement', 'capture'], true)) {
                        app('order.service')->markAsPaid(
                            $order,
                            'midtrans',
                            $transactionId,
                            orderId: $orderId,
                        );

                        SendOrderEmail::dispatch($order, 'confirmation');
                        SendOrderEmail::dispatch($order, 'download');
                    }
                } catch (Exception $e) {
                    Log::warning('Finish redirect: Midtrans status check failed', [
                        'order_id' => $internalId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('payment.success');
    }

    public function unfinishRedirect()
    {
        return redirect()->route('payment.pending');
    }

    private function handleCallback(Request $request)
    {
        try {
            $notification = app('payment')->callback($request->all());

            $orderId = str_replace('ORDER-', '', $notification->orderId);
            $orderId = (int) explode('-', $orderId)[0];

            $order = Order::findOrFail($orderId);

            $rawData = $notification->rawData;
            $transactionStatus = $notification->transactionStatus;
            $fraudStatus = $rawData['fraud_status'] ?? null;

            $isPaid = $transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

            if ($isPaid) {
                app('order.service')->markAsPaid(
                    $order,
                    'midtrans',
                    $notification->transactionId,
                    orderId: $notification->orderId,
                );

                SendOrderEmail::dispatch($order, 'confirmation');
                SendOrderEmail::dispatch($order, 'download');
            }

            $isFailed = in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true);

            if ($isFailed && $order->status !== OrderStatus::Paid) {
                $order->update(['status' => OrderStatus::Failed]);
            }

            if ($transactionStatus === 'refund' || $transactionStatus === 'partial_refund') {
                $order->update(['status' => OrderStatus::Refunded]);
            }

            return response('OK', 200);
        } catch (Exception $e) {
            Log::error('Midtrans callback failed: '.$e->getMessage(), [
                'order_id' => $request->input('order_id'),
                'transaction_id' => $request->input('transaction_id'),
            ]);

            return response('OK', 200);
        }
    }
}
