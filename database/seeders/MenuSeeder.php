<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Biostate\FilamentMenuBuilder\Enums\MenuItemType;
use Biostate\FilamentMenuBuilder\Models\Menu;
use Illuminate\Database\Seeder;

final class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $mainMenu = Menu::create(['name' => 'Main Menu']);

        $mainMenu->items()->createMany([
            [
                'name' => [
                    'en' => 'Products',
                    'id' => 'Produk',
                ],
                'type' => MenuItemType::Route,
                'route' => 'products.index',
                'route_parameters' => collect([]),
                'target' => '_self',
            ],
            [
                'name' => [
                    'en' => 'Blog',
                    'id' => 'Blog',
                ],
                'type' => MenuItemType::Route,
                'route' => 'blog.index',
                'route_parameters' => collect([]),
                'target' => '_self',
            ],
        ]);

        $footerMenu = Menu::create(['name' => 'Footer Menu']);

        $menuableType = app(Page::class)->getMorphClass();

        $footerMenu->items()->createMany([
            [
                'name' => [
                    'en' => 'About Us',
                    'id' => 'Tentang Kami',
                ],
                'menuable_type' => $menuableType,
                'menuable_id' => 1,
                'type' => MenuItemType::Model,
                'route' => 'pages.show',
                'route_parameters' => collect([['key' => 'page', 'value' => 'about']]),
                'target' => '_self',
            ],
            [
                'name' => [
                    'en' => 'Contact',
                    'id' => 'Hubungi Kami',
                ],
                'menuable_type' => $menuableType,
                'menuable_id' => 2,
                'type' => MenuItemType::Model,
                'route' => 'pages.show',
                'route_parameters' => collect([['key' => 'page', 'value' => 'contact']]),
                'target' => '_self',
            ],
            [
                'name' => [
                    'en' => 'Privacy Policy',
                    'id' => 'Kebijakan Privasi',
                ],
                'menuable_type' => $menuableType,
                'menuable_id' => 4,
                'type' => MenuItemType::Model,
                'route' => 'pages.show',
                'route_parameters' => collect([['key' => 'page', 'value' => 'privacy-policy']]),
                'target' => '_self',
            ],
            [
                'name' => [
                    'en' => 'Terms of Service',
                    'id' => 'Kebijakan Layanan',
                ],
                'menuable_type' => $menuableType,
                'menuable_id' => 5,
                'type' => MenuItemType::Model,
                'route' => 'pages.show',
                'route_parameters' => collect([['key' => 'page', 'value' => 'terms-of-service']]),
                'target' => '_self',
            ],
            [
                'name' => [
                    'en' => 'DMCA',
                    'id' => 'DMCA',
                ],
                'menuable_type' => $menuableType,
                'menuable_id' => 3,
                'type' => MenuItemType::Model,
                'route' => 'pages.show',
                'route_parameters' => collect([['key' => 'page', 'value' => 'dmca']]),
                'target' => '_self',
            ],
            [
                'name' => [
                    'en' => 'Refund Policy',
                    'id' => 'Kebijakan Pengembalian',
                ],
                'menuable_type' => $menuableType,
                'menuable_id' => 6,
                'type' => MenuItemType::Model,
                'route' => 'pages.show',
                'route_parameters' => collect([['key' => 'page', 'value' => 'refund-policy']]),
                'target' => '_self',
            ],
        ]);
    }
}
