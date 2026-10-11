<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

final class OrderAccessService
{
    /**
     * Issue a fresh Order Access Token, replacing any existing one.
     *
     * Only the token's hash is persisted; the plaintext is returned once, for
     * embedding in the Order Access Page URL.
     */
    public function issue(Order $order): string
    {
        $token = Str::random(64);

        $order->forceFill([
            'access_token_hash' => $this->hash($token),
            'access_token_issued_at' => now(),
        ])->save();

        return $token;
    }

    /**
     * Rotate the token, invalidating the previously issued one.
     */
    public function rotate(Order $order): string
    {
        return $this->issue($order);
    }

    /**
     * Resolve the Order a plaintext token grants access to, if any.
     */
    public function resolve(string $token): ?Order
    {
        if ($token === '') {
            return null;
        }

        return Order::query()
            ->where('access_token_hash', $this->hash($token))
            ->first();
    }

    private function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
