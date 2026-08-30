{{--
  utility-classes/_elevate
  Always-on .elevate / .elevate-sm / .elevate-lg + radius helpers for wrapped cards.
  Depends on: _tokens
--}}
<style id="webkernel-utility-elevate">
/* Base transition for elevate utilities */
.transition-elevate,
.elevate,
.elevate-sm,
.elevate-lg,
.hover\:elevate,
.hover\:elevate-sm,
.hover\:elevate-lg {
    transition-property: transform, box-shadow;
    transition-duration: var(--wds-utility-duration, 0.2s);
    transition-timing-function: var(--wds-utility-ease, cubic-bezier(0.4, 0, 0.2, 1));
}

/* Paired with hover:*-border: snap ring color (no box-shadow tween through black) */
.hover\:elevate[class*="-border"],
.hover\:elevate-sm[class*="-border"],
.hover\:elevate-lg[class*="-border"] {
    transition-property: transform;
}

/*
 * Elevation shadow follows the elevated surface's border-radius.
 * Utility on a transparent wrapper around a card (.fi-section, .wk-stat):
 * adopt that child's radius so the hover shadow is not square.
 */
.elevate:has(> .fi-section:not(.fi-compact)),
.elevate-sm:has(> .fi-section:not(.fi-compact)),
.elevate-lg:has(> .fi-section:not(.fi-compact)),
.hover\:elevate:has(> .fi-section:not(.fi-compact)),
.hover\:elevate-sm:has(> .fi-section:not(.fi-compact)),
.hover\:elevate-lg:has(> .fi-section:not(.fi-compact)) {
    border-radius: var(--radius-lg);
}
.elevate:has(> .fi-section.fi-compact),
.elevate-sm:has(> .fi-section.fi-compact),
.elevate-lg:has(> .fi-section.fi-compact),
.hover\:elevate:has(> .fi-section.fi-compact),
.hover\:elevate-sm:has(> .fi-section.fi-compact),
.hover\:elevate-lg:has(> .fi-section.fi-compact) {
    border-radius: var(--radius-lg);
}
.elevate:has(> .wk-stat),
.elevate-sm:has(> .wk-stat),
.elevate-lg:has(> .wk-stat),
.hover\:elevate:has(> .wk-stat),
.hover\:elevate-sm:has(> .wk-stat),
.hover\:elevate-lg:has(> .wk-stat) {
    border-radius: var(--radius-lg) !important;
}

/* Always-on elevation */
.elevate-sm {
    transform: translateY(var(--wds-elevate-y-sm, -1px));
    box-shadow: var(--wds-shadow-sm);
}
.elevate {
    transform: translateY(var(--wds-elevate-y, -2px));
    box-shadow: var(--wds-shadow-md);
}
.elevate-lg {
    transform: translateY(var(--wds-elevate-y-lg, -4px));
    box-shadow: var(--wds-shadow-lg);
}

/*
 * Hover elevate — single ring layer only:
 *   0 0 0 1px var(--wds-hover-border-color | default gray)
 *   + lift shadow (no second black ring — avoids black→primary flash)
 */
@media (hover: hover) {
    .hover\:elevate-sm:hover {
        transform: translateY(var(--wds-elevate-y-sm, -1px));
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-sm-lift);
    }
    .hover\:elevate:hover {
        transform: translateY(var(--wds-elevate-y, -2px));
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-md-lift);
    }
    .hover\:elevate-lg:hover {
        transform: translateY(var(--wds-elevate-y-lg, -4px));
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-lg-lift);
    }
}
</style>
