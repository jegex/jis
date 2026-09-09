<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'release_date')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('release_date');
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'is_preorder')) {
                $table->boolean('is_preorder')->default(false)->after('scheduled_at');
            }

            if (! Schema::hasColumn('products', 'preorder_duration')) {
                $table->unsignedInteger('preorder_duration')->nullable()->after('is_preorder');
            }

            if (! Schema::hasColumn('products', 'preorder_interval')) {
                $table->string('preorder_interval')->nullable()->after('preorder_duration');
            }
        });
    }

    public function down(): void
    {
        $columns = ['is_preorder', 'preorder_duration', 'preorder_interval'];

        Schema::table('products', function (Blueprint $table) use ($columns): void {
            $table->dropColumn(array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('products', $column),
            )));
        });
    }
};
