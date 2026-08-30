{{-- webkernel::panels.layout.sidebar._base — internal padding, border-right, hidden scrollbar (private partial)

    Filament still *defines* --collapsed-sidebar-width in <head> (default 4.5rem) but its
    compiled CSS never *applies* it for collapsible-on-desktop (only w-(--sidebar-width)
    when open). Setting the var alone does nothing — we must set width on the closed rail.
--}}
<style>
:root {
    --collapsed-sidebar-width: 4rem;
}

/* Desktop collapsible rail: actually use the var Filament only declares */
@media (min-width: 1024px) {
    .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar.fi-main-sidebar:not(.fi-sidebar-open),
    .fi-body.fi-body-has-sidebar-collapsible-on-desktop aside.fi-sidebar:not(.fi-sidebar-open) {
        width: var(--collapsed-sidebar-width) !important;
        min-width: var(--collapsed-sidebar-width) !important;
        max-width: var(--collapsed-sidebar-width) !important;
    }

    /* Icon rail: kill horizontal padding so 1.5rem is usable */
    .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-inline: 0 !important;
        overflow-x: hidden !important;
    }

    .fi-body.fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header {
        padding-inline: 0 !important;
    }
}

/*
 * Open sidebar: horizontal inset from --wds-sidebar-padding-x (tokens).
 * Do NOT set padding-inline: 0 after padding-left/right — logical props win
 * later in the cascade and zero the inset (sidebar looks flush / “no padding”).
 * Collapsed rail still zeroes padding in the media query above.
 */
.fi-sidebar-nav {
    padding-inline:     var(--wds-sidebar-padding-x) !important;
    position:           relative !important;
    overflow-y:         auto !important;
    overflow-x:         auto !important;
    padding-block: calc(var(--spacing) * 4);
    scrollbar-width:    none !important;
    -ms-overflow-style: none !important;
}
.fi-sidebar-nav::-webkit-scrollbar { width: 0 !important; height: 0 !important; }
.fi-sidebar-nav-groups  { row-gap: calc(var(--spacing) * 2.5); }
.fi-sidebar-footer      { row-gap: calc(var(--spacing) * 2.5); }

/*
 * Filament default (layout.css): only ps-5 + pt-5 on the mobile open-sidebar
 * control — padding on top/start, nothing on bottom. Looks broken next to our
 * mobile logo strip and leaves a lopsided gap. Equal inset all around.
 */
.fi-body > .fi-layout-sidebar-toggle-btn-ctn {
    padding-block: 0.75rem !important;
    padding-inline: 0.75rem !important;
}
</style>
