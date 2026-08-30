{{--
    Collapsed sidebar logo (shown when Alpine $store.sidebar.isOpen is false).

    Props:
        $logoUrl  string  image src (favicon / mark)
        $tooltip   string  brand name for x-tooltip (optional)

    Registered via Panel::sidebarLogo() → SIDEBAR_LOGO_AFTER.
--}}
@php
    $logoUrl = (string) ($logoUrl ?? '');
    $tooltip = trim((string) ($tooltip ?? ''));
@endphp

@if ($logoUrl !== '')
    @once
        @assets
            <style>
                .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header {
                    flex-wrap: wrap !important;
                    margin-top: 1.2rem;
                    margin-bottom: 0.25rem;
                    height: 80%;
                }
                .fi-sidebar:not(.fi-sidebar-open) .sidebar-mobile-logo-header-end {
                    width: 34px;
                    height: auto;
                    padding-top: 1rem;
                }
            </style>
        @endassets
    @endonce

    <div style="-webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;"
        x-show="! $store.sidebar.isOpen"
        x-cloak
        class="w-full flex justify-center items-center mt-3"
        x-tooltip="@js($tooltip) ? { content: @js($tooltip), theme: $store.theme } : false"
    >
        <img
            src="{{ $logoUrl }}"
            class="mx-auto block sidebar-mobile-logo-header-end"
            style="width: 34px; height: auto;"
            alt="{{ $tooltip !== '' ? $tooltip : 'Logo' }}"
        >
    </div>
@endif
