<style id="webkernel-utility-appear">

/* ─── .hover:appear — base state: hidden ────────────────────── */
.hover\:appear {
    display: inline-block;
    overflow: hidden;
    min-width: 0;
    width: 0;
    max-width: 0;
    opacity: 0;
    white-space: nowrap;
    vertical-align: middle;
    pointer-events: none;
    transition-property: width, max-width, min-width, opacity;
    transition-duration: var(--wds-utility-duration, 0.2s);
    transition-timing-function: var(--wds-utility-ease, cubic-bezier(0.4, 0, 0.2, 1));
}

*:has(> .hover\:appear),
*:has(.hover\:appear) {
    overflow: visible !important;
}

.fi-btn:has(.hover\:appear) {
    gap: 0;
}

/* ─── .hide:mobile — visible desktop, gone mobile ────────────── */
@media (max-width: 767px) {
    .hide\:mobile {
        display: none !important;
    }
}
/* ─── .hide:tablet — visible mobile+desktop, gone tablet ─────── */
@media (min-width: 768px) and (max-width: 1023px) {
    .hide\:tablet {
        display: none !important;
    }
}
/* ─── .hide:desktop — visible mobile+tablet, gone desktop ────── */
@media (min-width: 1024px) {
    .hide\:desktop {
        display: none !important;
    }
}
/* ─── .hide:all — always hidden ─────────────────────────────── */
.hide\:all {
    display: none !important;
}

/* ─── DESKTOP (>= 768px) ─────────────────────────────────────── */
@media (min-width: 768px) {

    /* Neutralize tooltip chrome when both classes present */
    .hover\:appear.mobile\:appear {
        position: static !important;
        inset: auto !important;
        transform: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        color: inherit !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
        z-index: auto !important;
        border-radius: 0 !important;
    }

    /* Reveal — spécificité renforcée avec :where() pour ne pas écraser l'état caché */
    :is(
        .fi-btn,
        .fi-dropdown-trigger,
        button,
        [role="button"],
        a
    ):is(:hover, :focus-visible, :focus-within) .hover\:appear,
    *:is(:hover, :focus-visible, :focus-within) > .hover\:appear {
        width: max-content !important;
        min-width: max-content !important;
        max-width: 24rem !important;
        opacity: 1 !important;
        overflow: visible !important;
    }

    .fi-btn:has(.hover\:appear):is(:hover, :focus-visible, :focus-within) {
        gap: 0.25rem;
    }
}

/* ─── MOBILE (< 768px) ───────────────────────────────────────── */
@media (max-width: 767px) {

    *:has(> .hover\:appear.mobile\:appear) {
        position: relative !important;
    }

    .hover\:appear.mobile\:appear {
        position: absolute !important;
        bottom: calc(100% + 0.4rem) !important;
        left: 50% !important;
        z-index: 50 !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 0.375rem !important;
        background: var(--gray-950, #0a0a0a) !important;
        box-shadow: var(--wds-shadow-md, 0 6px 14px -4px rgb(0 0 0 / 0.12)) !important;
        color: #fff !important;
        font-size: 0.75rem !important;
        font-weight: 500 !important;
        line-height: 1.25 !important;
        transform: translateX(-50%) !important;
    }

    .dark .hover\:appear.mobile\:appear,
    html.dark .hover\:appear.mobile\:appear,
    [data-theme="dark"] .hover\:appear.mobile\:appear,
    .fi-dark .hover\:appear.mobile\:appear {
        background: var(--gray-100, #f3f4f6) !important;
        color: var(--gray-950, #0a0a0a) !important;
    }

    *:is(:hover, :focus, :focus-visible, :focus-within, :active) .hover\:appear.mobile\:appear {
        width: max-content !important;
        min-width: max-content !important;
        max-width: min(16rem, 80vw) !important;
        opacity: 1 !important;
    }
}
</style>
