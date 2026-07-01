<ul class="flex items-center space-x-4 list-none m-0 p-0">
    @foreach($menuItems ?? [] as $menuItem)
        @include('components.menu.header-item', ['item' => $menuItem, 'isNested' => false])
    @endforeach
</ul>
