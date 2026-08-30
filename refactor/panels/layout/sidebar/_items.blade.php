{{-- webkernel::panels.layout.sidebar._items
     Generic panel nav items, groups, labels, active state, margins (private partial).

     Plugin-specific sidebar chrome (mdsite tree, overview badge, index child button,
     parent-row, dual rail icons) lives in plugin-mdsite sidebar item overrides —
     not here.
--}}

@php
    /*
    |--------------------------------------------------------------------------
    | Sidebar CSS configuration — tweak these PHP values directly.
    | They are injected as inline CSS custom properties on :root,
    | so every rule in this file that references var(--si-*) picks them up
    | automatically without touching the selectors below.
    |--------------------------------------------------------------------------
    */

    // ── Rail (collapsed) button square size ───────────────────────────────
    $rail_btn_size = '2.5rem'; // width & height of every collapsed icon button

    // ── Rail icon size ─────────────────────────────────────────────────────
    $rail_icon_size = '1.5rem'; // width & height of svg icons in collapsed rail

    // ── Top-level item margins (open sidebar) ──────────────────────────────
    $item_margin_start = 'var(--wds-sidebar-item-margin-left)'; // margin-inline-start
    $item_margin_end = 'calc(var(--wds-sidebar-item-margin-right) * 1.4)'; // margin-inline-end

    // ── Nested child indent (open sidebar) ────────────────────────────────
    $child_indent = '1.25rem'; // padding-inline-start on .fi-sidebar-sub-group-items

    // ── Top-level button chrome (open sidebar) ─────────────────────────────
    $btn_border_radius = 'var(--radius-lg)'; // border-radius on top-level item btns
    $btn_padding_block = '0.4rem'; // padding-top / padding-bottom
    $btn_padding_inline = '0.6rem'; // padding-left / padding-right

    // ── Sub-grouped item opacity ───────────────────────────────────────────
    $sub_item_opacity = '0.78'; // opacity for nested tree labels at rest

    // ── Flyout panel dimensions ────────────────────────────────────────────
    $flyout_min_width = '16rem';
    $flyout_max_width = 'min(92vw, 32rem)';

    // ── Active primary colors ──────────────────────────────────────────────
    $active_color_light = 'var(--primary-700)';
    $active_color_dark = 'var(--primary-400)';
    $active_parent_color_light = 'var(--primary-600)';
    $active_parent_color_dark = 'var(--primary-400)';
@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | Inject PHP config as CSS custom properties
    |--------------------------------------------------------------------------
    */
    :root {
        --si-rail-btn-size: {{ $rail_btn_size }};
        --si-rail-icon-size: {{ $rail_icon_size }};
        --si-child-indent: {{ $child_indent }};
        --si-btn-border-radius: {{ $btn_border_radius }};
        --si-btn-padding-block: {{ $btn_padding_block }};
        --si-btn-padding-inline: {{ $btn_padding_inline }};
        --si-sub-item-opacity: {{ $sub_item_opacity }};
        --si-flyout-min-width: {{ $flyout_min_width }};
        --si-flyout-max-width: {{ $flyout_max_width }};
        --si-active-color-light: {{ $active_color_light }};
        --si-active-color-dark: {{ $active_color_dark }};
        --si-active-parent-light: {{ $active_parent_color_light }};
        --si-active-parent-dark: {{ $active_parent_color_dark }};
    }

/* ── Open sidebar: labels + top-level chrome ─────────────────────────────── */

.fi-sidebar-group-label {
    font-weight: 400 !important;
}

aside .fi-sidebar-item:not(.fi-sidebar-item--sub-grouped),
aside .fi-sidebar-group-btn {
    /* Logical margins — flip correctly under dir=rtl (top-level only) */
    margin-inline-start: {{ $item_margin_start }} !important;
    margin-inline-end: {{ $item_margin_end }} !important;
}

/* Nested tree rows: no extra side margin (indent comes from the sub-group ul) */
aside .fi-sidebar-item.fi-sidebar-item--sub-grouped {
    margin-inline: 0 !important;
}

/*
 * Top-level only: leaf links + expand-only parent buttons share button chrome.
 * Tree children (.fi-sidebar-item--sub-grouped) stay plain — no pill padding /
 * border / fill (see override below; Filament still styles every .fi-sidebar-item-btn).
 */
.fi-sidebar-item.fi-sidebar-item-has-url:not(.fi-sidebar-item--sub-grouped) > .fi-sidebar-item-btn,
.fi-sidebar-item:not(.fi-sidebar-item--sub-grouped) > button.fi-sidebar-item-btn {
    border: 1px solid transparent;
    border-radius: var(--si-btn-border-radius);
    padding-block: var(--si-btn-padding-block);
    padding-inline: var(--si-btn-padding-inline);
    transition: border-color 0.15s ease;
}

.fi-sidebar-item.fi-sidebar-item-has-url:not(.fi-sidebar-item--sub-grouped) > .fi-sidebar-item-btn:hover,
.fi-sidebar-item.fi-active.fi-sidebar-item-has-url:not(.fi-sidebar-item--sub-grouped) > .fi-sidebar-item-btn,
.fi-sidebar-item:not(.fi-sidebar-item--sub-grouped) > button.fi-sidebar-item-btn:hover,
.fi-sidebar-item:not(.fi-sidebar-item--sub-grouped).fi-sidebar-item-has-active-child-items > button.fi-sidebar-item-btn {
    border-color: color-mix(in oklab, currentColor 6%, transparent);
}

/*
 * Nested tree rows: text-like, not buttons (generic Filament nesting).
 * Plugin parent-row overrides live in mdsite.
 */
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn {
    padding-inline-start: 0 !important;
    padding-inline-end: 9.65px;
    border: 0;
    border-radius: 0;
    background-color: transparent;
}

.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn > .fi-sidebar-item-label,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn > .fi-sidebar-item-chevron-icon,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn > .fi-sidebar-item-badge-ctn {
    opacity: var(--si-sub-item-opacity);
    transition: opacity 0.15s ease;
}

.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:hover > .fi-sidebar-item-label,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:hover > .fi-sidebar-item-chevron-icon,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:hover > .fi-sidebar-item-badge-ctn,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:focus-visible > .fi-sidebar-item-label,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:focus-visible > .fi-sidebar-item-chevron-icon,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:focus-visible > .fi-sidebar-item-badge-ctn,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-label,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-chevron-icon,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-badge-ctn,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn > .fi-sidebar-item-label,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn > .fi-sidebar-item-chevron-icon,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn > .fi-sidebar-item-badge-ctn {
    opacity: 1;
}

.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:hover,
.fi-sidebar-item.fi-sidebar-item--sub-grouped > .fi-sidebar-item-btn:focus-visible,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-active > .fi-sidebar-item-btn,
.fi-sidebar-item.fi-sidebar-item--sub-grouped.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn {
    background-color: transparent;
    border-color: transparent;
}

/*
 * Open sidebar: same nest step at every depth (nested <ul>s stack).
 */
.fi-sidebar-sub-group-items:not(.fi-sidebar-sub-group-items--collapsed),
.fi-sidebar-sub-group-items:not(.fi-sidebar-sub-group-items--collapsed)
    .fi-sidebar-sub-group-items {
    padding-inline-start: var(--si-child-indent);
}

/* ── Collapsed rail (desktop) ────────────────────────────────────────────── */

@media (min-width: 1024px) {
    /*
     * Collapsed rail: true horizontal center.
     * Offenders: asymmetric item margins, has-url padding, scrollbar-gutter,
     * Filament flex-col on .fi-active / .fi-sidebar-item-has-active-child-items,
     * and dropdown wrappers that do not stretch full width.
     */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav {
        scrollbar-gutter: auto !important;
    }

    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav-groups {
        margin-inline: 0 !important;
    }

    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-btn {
        margin-inline: 0 !important;
    }

    /*
     * Filament sets flex-col on .fi-active and .fi-sidebar-item-has-active-child-items
     * (sidebar.css). That makes justify-content center vertically and stretch children
     * full-width → active icon looks left-shifted vs inactive row-centered peers.
     * Force row + center for every collapsed rail item, including active.
     */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-sidebar-item-has-active-child-items {
        display: flex !important;
        flex-direction: row !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
    }

    /*
     * Square centered buttons for ALL collapsed states:
     * default, has-url, active, active+has-url, dropdown trigger.
     */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-dropdown-trigger-btn,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-sidebar-item-has-url > .fi-sidebar-item-btn,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active.fi-sidebar-item-has-url > .fi-sidebar-item-btn,
    .fi-sidebar:not(.fi-sidebar-open) li.fi-sidebar-item.fi-active.fi-sidebar-item-has-url > a.fi-sidebar-item-btn {
        width: var(--si-rail-btn-size) !important;
        min-width: var(--si-rail-btn-size) !important;
        max-width: var(--si-rail-btn-size) !important;
        height: var(--si-rail-btn-size) !important;
        min-height: var(--si-rail-btn-size) !important;
        display: flex !important;
        flex-direction: row !important;
        justify-content: center !important;
        align-items: center !important;
        align-self: center !important;
        margin: 0 auto !important;
        padding: 0 !important;
        gap: 0 !important;
        box-sizing: border-box !important;
        border-radius: var(--radius-md);
    }

    /* Hide open-sidebar chrome when rail dropdown is present; hide expanded children */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item:has(> div .fi-dropdown) > .fi-sidebar-item-btn,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item > .fi-sidebar-sub-group-items {
        display: none !important;
    }

    /* Rail dropdown trigger chain: div → .fi-dropdown → .fi-dropdown-trigger → button */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item > div:has(.fi-dropdown),
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item .fi-dropdown,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item .fi-dropdown-trigger {
        display: flex !important;
        flex-direction: row !important;
        width: 100% !important;
        justify-content: center !important;
        align-items: center !important;
        margin-inline: 0 !important;
    }

    /* Collapsed: hide tree markers that only exist for the open tree UI */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-grouped-border--open-only {
        display: none !important;
    }

    /* Collapsed: nested icons match top-level rail size */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-sub-group-items .fi-sidebar-item-icon {
        display: block !important;
        flex-shrink: 0 !important;
        width: var(--si-rail-icon-size) !important;
        height: var(--si-rail-icon-size) !important;
        min-width: var(--si-rail-icon-size) !important;
        min-height: var(--si-rail-icon-size) !important;
        margin-inline: 0 !important;
    }

    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-sub-group-items .fi-sidebar-item-icon svg {
        width: var(--si-rail-icon-size) !important;
        height: var(--si-rail-icon-size) !important;
    }

    /*
     * Active icon color on collapsed rail.
     * Filament only styles: .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon
     * (direct child). Rail path wraps the btn in div > dropdown > trigger, so primary
     * never applied. Re-apply for any nested .fi-sidebar-item-btn under an active item.
     */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active .fi-sidebar-item-btn > .fi-icon,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active .fi-sidebar-item-btn > .fi-sidebar-item-icon,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-icon--active {
        color: var(--si-active-color-light) !important;
    }

    .dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active .fi-sidebar-item-btn > .fi-icon,
    .dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active .fi-sidebar-item-btn > .fi-sidebar-item-icon,
    .dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-icon--active,
    .fi-body.dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-active .fi-sidebar-item-btn > .fi-icon,
    .fi-body.dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-icon--active {
        color: var(--si-active-color-dark) !important;
    }

    /* Parent of the current page (collapsed): same primary affordance on the rail icon */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-sidebar-item-has-active-child-items:not(.fi-active) .fi-sidebar-item-btn > .fi-icon,
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-sidebar-item-has-active-child-items:not(.fi-active) .fi-sidebar-item-btn > .fi-sidebar-item-icon {
        color: var(--si-active-parent-light) !important;
    }

    .dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-sidebar-item-has-active-child-items:not(.fi-active) .fi-sidebar-item-btn > .fi-icon,
    .dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-sidebar-item-has-active-child-items:not(.fi-active) .fi-sidebar-item-btn > .fi-sidebar-item-icon,
    .fi-body.dark .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item.fi-sidebar-item-has-active-child-items:not(.fi-active) .fi-sidebar-item-btn > .fi-icon {
        color: var(--si-active-parent-dark) !important;
    }
}

/*
 * Collapsed-rail flyouts (teleported → <body>, so CSS is global).
 * Dropdown: width="fi-sidebar-rail-panel"
 *
 * Pure CSS equal-width menu:
 *   1. panel = fit-content, min 16rem, max 92vw / 32rem
 *   2. list  = column flex, stretch children
 *   3. row   = full width flex; label flexes; trailing never shrinks
 *   4. trailing uses margin-inline-start:auto → always at inline-end (LTR + RTL)
 *   5. overflow stays inside the panel — chevron/badge never paint outside
 */
.fi-dropdown-panel.fi-sidebar-rail-panel {
    width: fit-content !important;
    min-width: var(--si-flyout-min-width) !important;
    max-width: var(--si-flyout-max-width) !important;
    box-sizing: border-box !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
}

/* Column of full-width rows (kill Filament list grid) */
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-list {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    width: 100% !important;
    min-width: var(--si-flyout-min-width) !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* Nested submenu: wrapper fills the row */
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-list > .fi-dropdown {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown > .fi-dropdown-trigger {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* Row: [icon][label…………][badge chevron] */
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-list-item {
    display: flex !important;
    flex-flow: row nowrap !important;
    align-items: center !important;
    justify-content: flex-start !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    gap: 0.5rem !important;
    overflow: hidden !important;
}

.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-list-item > .fi-icon:first-child,
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-list-item > .fi-dropdown-list-item-image {
    flex: 0 0 auto !important;
}

/*
 * Preferred size = content (grows the fit-content panel).
 * Can shrink when panel hits max-width → ellipsis, trailing stays visible.
 */
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-list-item-label {
    flex: 1 1 max-content !important;
    min-width: 0 !important;
    max-width: 100% !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    text-align: start !important;
}

/* Badge + chevron — never shrink, always at inline-end */
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-sidebar-rail-item-trailing {
    display: inline-flex !important;
    flex: 0 0 auto !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 0.375rem !important;
    margin-inline-start: auto !important;
}

.fi-dropdown-panel.fi-sidebar-rail-panel .fi-sidebar-rail-item-trailing .fi-badge,
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-sidebar-rail-item-trailing .fi-icon,
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-sidebar-rail-item-trailing svg {
    flex: 0 0 auto !important;
}

/* Header row same contract */
.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-header {
    display: flex !important;
    flex-flow: row nowrap !important;
    align-items: center !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
}

.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-header > .fi-icon {
    flex: 0 0 auto !important;
}

.fi-dropdown-panel.fi-sidebar-rail-panel .fi-dropdown-header > span {
    flex: 1 1 max-content !important;
    min-width: 0 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    text-align: start !important;
}
</style>
