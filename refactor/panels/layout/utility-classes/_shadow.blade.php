{{--
  utility-classes/_shadow
  Always-on + hover shadow utilities.
  Depends on: _tokens
--}}
<style id="webkernel-utility-shadow">
.shadow-sm,
.shadow,
.shadow-md,
.shadow-lg,
.shadow-xl,
.hover\:shadow-sm,
.hover\:shadow,
.hover\:shadow-md,
.hover\:shadow-lg,
.hover\:shadow-xl {
    transition-property: box-shadow;
    transition-duration: var(--wds-utility-duration, 0.2s);
    transition-timing-function: var(--wds-utility-ease, cubic-bezier(0.4, 0, 0.2, 1));
}

.shadow-sm { box-shadow: var(--wds-shadow-sm); }
.shadow    { box-shadow: var(--wds-shadow); }
.shadow-md { box-shadow: var(--wds-shadow-md); }
.shadow-lg { box-shadow: var(--wds-shadow-lg); }
.shadow-xl { box-shadow: var(--wds-shadow-xl); }
.shadow-none { box-shadow: none; }

@media (hover: hover) {
    .hover\:shadow-sm:hover {
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-sm-lift);
    }
    .hover\:shadow:hover {
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-lift);
    }
    .hover\:shadow-md:hover {
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-md-lift);
    }
    .hover\:shadow-lg:hover {
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-lg-lift);
    }
    .hover\:shadow-xl:hover {
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-xl-lift);
    }
    .hover\:shadow-none:hover { box-shadow: none; }
}
</style>
