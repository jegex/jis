<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('paid_at');
            $table->index('user_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('is_published');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index('is_published');
            $table->index('published_at');
            $table->index('category_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('type');
            $table->index('is_published');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->index('is_published');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_published']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['is_published']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['is_published']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['is_published']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gateway_transaction_id']);
        });
    }
};
