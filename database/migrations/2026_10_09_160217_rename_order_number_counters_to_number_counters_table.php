<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('order_number_counters', 'number_counters');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('number_counters', 'order_number_counters');
    }
};
