<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('email_logs');
    }

    public function down(): void
    {
        Schema::create('email_logs', function ($table) {
            $table->id();
            $table->foreignId('email_template_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('loggable');
            $table->string('sent_to');
            $table->string('status')->default('sent');
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }
};
