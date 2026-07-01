<ul class="flex flex-col gap-3">
    @foreach($menuItems ?? [] as $menuItem)
        <x-menu.menu-item :menuItem="$menuItem" />
    @endforeach
</ul>
