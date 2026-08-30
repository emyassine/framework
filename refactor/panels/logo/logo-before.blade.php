<style>
    .logo-before {
        display: flex; padding: 0.5rem; justify-content: center; align-items: center;
    }
    @media (min-width: 1024px) {
        .logo-before { display: none; }
    }
</style>
<div class="logo-before" style="-webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;">

    @if ($homeUrl = filament()->getHomeUrl())
        <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
            <x-filament-panels::logo />
        </a>
    @else
        <x-filament-panels::logo />
    @endif
</div>
