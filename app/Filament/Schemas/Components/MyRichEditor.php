<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Awcodes\RicherEditor\Plugins\DebugPlugin;
use Awcodes\RicherEditor\Plugins\EmbedPlugin;
use Awcodes\RicherEditor\Plugins\EmojiPlugin;
use Awcodes\RicherEditor\Plugins\FakerPlugin;
use Awcodes\RicherEditor\Plugins\FullScreenPlugin;
use Awcodes\RicherEditor\Plugins\IdPlugin;
use Awcodes\RicherEditor\Plugins\LinkPlugin;
use Awcodes\RicherEditor\Plugins\SourceCodePlugin;
use Filament\Forms\Components\RichEditor;

final class MyRichEditor
{
    public static function make(?string $name)
    {
        $plugins = [
            DebugPlugin::make(), // only works in local environment
            EmbedPlugin::make(),
            EmojiPlugin::make(), // Doesn't have a toolbar button
            FullScreenPlugin::make(),
            IdPlugin::make(), // Doesn't have a toolbar button
            LinkPlugin::make(), // Requires IdPlugin
            SourceCodePlugin::make(),
        ];

        $toolbarGroup = ['embed', 'sourceCode', 'fullscreen'];

        if (app()->environment('local')) {
            $plugins[] = FakerPlugin::make();
            $toolbarGroup = array_merge($toolbarGroup, ['fakeHeading', 'fakeParagraphs', 'fakeBulletList', 'fakeNumberedList']);
        }

        return RichEditor::make($name)
            ->plugins($plugins)
            ->enableToolbarButtons([$toolbarGroup])
            ->maxHeight('400px');
    }
}
