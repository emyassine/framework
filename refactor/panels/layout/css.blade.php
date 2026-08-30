{{--
    webkernel::panels.layout.css
    ────────────────────────────
    Entry point — includes only, no styles here.
    Injected at PanelsRenderHook::BODY_START (once per request, Octane-safe).
--}}

{{-- Design tokens --}}
@once
    <style>
    :not(.fi-body-has-topbar) .fi-sidebar-header .fi-logo {
        margin-inline-start: calc(var(--spacing) * 1) !important;
    }
    </style>
    @includeIf('webkernel::panels.layout._tokens')
    {{-- Typography (WTS) is injected in HEAD via FilamentRenderHooks::STYLES_AFTER --}}

    {{-- Page shell --}}
    @includeIf('webkernel::panels.layout._page')

    {{-- Topbar --}}
    @includeIf('webkernel::panels.layout.topbar._base')
    @includeIf('webkernel::panels.layout.topbar._colors')

    {{-- Sidebar --}}
    @includeIf('webkernel::panels.layout.sidebar._base')
    @includeIf('webkernel::panels.layout.sidebar._items')
    @includeIf('webkernel::panels.layout.sidebar._desktop')

    {{-- Main content area --}}
    @includeIf('webkernel::panels.layout._main')

    {{-- Global components --}}
    @includeIf('webkernel::panels.layout._table')
    @includeIf('webkernel::panels.layout._scrollbar')
    @includeIf('webkernel::panels.layout._modal')

    {{-- Opt-in utilities (hover:elevate, hover:primary-border, …) --}}
    @includeIf('webkernel::panels.layout.utility-classes.index')

    {{-- Stats elements --}}
    @includeIf('webkernel::panels.layout.components._stats')
    @includeIf('webkernel::panels.layout.components.triptych')

    {{-- JS hooks --}}
    @includeIf('webkernel::panels.layout._script')

    <style>
    /* Landmark focus target for skip-link — no giant page outline */
        #fi-main-content:focus,
        #fi-main-content:focus-visible,
        .fi-main:focus,
        .fi-main:focus-visible,
        .fi-simple-main:focus,
        .fi-simple-main:focus-visible {
            outline: none;
        }
    </style>


    @if(false)
        <style>
        .fi-page-main::before {
            content: "";
            display: block;
            height: 200px; /* hauteur fixe ou ajustable */
            background-image: url("https://images.blush.design/b4cde6b173a7719b63c170a6a9f58ee0?w=920&auto=compress&cs=srgb");
            background-size: contain;     /* garde les proportions */
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Variante en dark mode : inversion des couleurs */
        .dark .fi-page-main::before,
        [data-theme="dark"] .fi-page-main::before {
            filter: invert(0.7) brightness(1.2) contrast(0.9);
        }


        </style>
    @endif
@endonce
