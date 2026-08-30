{{--
  utility-classes/_hover-motion
  hover:scale-*, hover:lift-*, hover:brightness-up, hover:opacity-*
  Depends on: _tokens
--}}
<style id="webkernel-utility-hover-motion">
.hover\:scale-sm,
.hover\:scale,
.hover\:scale-lg,
.hover\:lift-sm,
.hover\:lift,
.hover\:lift-lg,
.active\:press {
    transition-property: transform, filter, opacity;
    transition-duration: var(--wds-utility-duration, 0.2s);
    transition-timing-function: var(--wds-utility-ease, cubic-bezier(0.4, 0, 0.2, 1));
}

@media (hover: hover) {
    .hover\:scale-sm:hover { transform: scale(1.02); }
    .hover\:scale:hover    { transform: scale(1.04); }
    .hover\:scale-lg:hover { transform: scale(1.06); }

    .hover\:lift-sm:hover { transform: translateY(var(--wds-elevate-y-sm, -1px)); }
    .hover\:lift:hover    { transform: translateY(var(--wds-elevate-y, -2px)); }
    .hover\:lift-lg:hover { transform: translateY(var(--wds-elevate-y-lg, -4px)); }

    .hover\:brightness-up:hover { filter: brightness(1.06); }
    .hover\:opacity-90:hover { opacity: 0.9; }
    .hover\:opacity-80:hover { opacity: 0.8; }
}

.active\:press:active {
    transform: translateY(0) scale(0.98);
}
</style>
