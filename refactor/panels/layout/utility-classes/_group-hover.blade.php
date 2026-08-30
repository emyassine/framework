{{--
  utility-classes/_group-hover
  Parent .group:hover → .group-hover:*
  Depends on: _tokens
--}}
<style id="webkernel-utility-group-hover">
@media (hover: hover) {
    .group:hover .group-hover\:elevate,
    .group:hover .group-hover\:elevate:is(*) {
        transform: translateY(var(--wds-elevate-y, -2px));
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-md-lift);
    }
    .group:hover .group-hover\:shadow-md {
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color, var(--wds-elevate-ring-default)),
            var(--wds-shadow-md-lift);
    }
    .group:hover .group-hover\:opacity-100 {
        opacity: 1;
    }
}
</style>
