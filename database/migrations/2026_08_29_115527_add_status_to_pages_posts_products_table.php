<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['pages', 'posts', 'products'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['is_published']);

                $table->string('status')->default('draft')->after('is_published');
                $table->timestamp('scheduled_at')->nullable()->after('status');
            });

            DB::table($table)->where('is_published', true)->update(['status' => 'publish']);

            Schema::table($table, function (Blueprint $table) {
                $table->index('status');
                $table->dropColumn('is_published');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['status']);

                $table->boolean('is_published')->default(false)->after('status');
            });

            DB::table($table)->where('status', 'publish')->update(['is_published' => true]);

            Schema::table($table, function (Blueprint $table) {
                $table->index('is_published');
                $table->dropColumn(['scheduled_at', 'status']);
            });
        }
    }
};
