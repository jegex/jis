<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

final class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled';

    protected $description = 'Publish content with status future whose scheduled_at date has passed';

    public function handle(): int
    {
        $published = 0;

        foreach ([Page::class, Post::class, Product::class] as $modelClass) {
            $published += $modelClass::query()
                ->withoutGlobalScope('published')
                ->where('status', ContentStatus::Future)
                ->where('scheduled_at', '<=', now())
                ->get()
                ->each(function (Model $model): void {
                    $model->status = ContentStatus::Publish;

                    if ($model instanceof Post && $model->published_at === null) {
                        $model->published_at = now();
                    }

                    $model->save();
                })
                ->count();
        }

        $this->info("Done. Published {$published} scheduled content.");

        return Command::SUCCESS;
    }
}
