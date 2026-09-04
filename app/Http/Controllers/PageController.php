<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;

final class PageController extends Controller
{
    public function __invoke(Page $page)
    {
        if (! $page->isPublished()) {
            abort(404);
        }

        return view('pages.page-show', compact('page'))
            ->with('model', $page);
    }
}
