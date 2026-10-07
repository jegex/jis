<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $productColumns = array_filter(
            ['is_preorder', 'preorder_duration', 'preorder_interval'],
            fn (string $column): bool => Schema::hasColumn('products', $column),
        );

        if ($productColumns !== []) {
            Schema::table('products', function (Blueprint $table) use ($productColumns): void {
                $table->dropColumn($productColumns);
            });
        }

        if (Schema::hasColumn('orders', 'preorder_released_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('preorder_released_at');
            });
        }

        DB::table('email_templates')->where('type', 'preorder_release')->delete();

        $preorderVariables = ['is_preorder', 'release_date', 'preorder_info'];
        $placeholderPattern = '/\{(?:'.implode('|', $preorderVariables).')\}\s*/u';

        foreach (DB::table('email_templates')->get() as $template) {
            $body = json_decode((string) $template->body, true);
            $variables = json_decode((string) $template->variables, true);
            $dirty = false;

            if (is_array($body)) {
                foreach ($body as $locale => $text) {
                    $cleaned = preg_replace($placeholderPattern, '', (string) $text);
                    if ($cleaned !== $text) {
                        $body[$locale] = $cleaned;
                        $dirty = true;
                    }
                }
            }

            if (is_array($variables)) {
                $filtered = array_values(array_diff($variables, $preorderVariables));
                if (count($filtered) !== count($variables)) {
                    $variables = $filtered;
                    $dirty = true;
                }
            }

            if ($dirty) {
                DB::table('email_templates')->where('id', $template->id)->update([
                    'body' => json_encode($body),
                    'variables' => json_encode($variables),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_preorder')->default(false)->after('scheduled_at');
            $table->unsignedInteger('preorder_duration')->nullable()->after('is_preorder');
            $table->string('preorder_interval')->nullable()->after('preorder_duration');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('preorder_released_at')->nullable()->after('paid_at');
        });
    }
};
