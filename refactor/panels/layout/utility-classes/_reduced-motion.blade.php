{{--
  utility-classes/_reduced-motion
  Disable motion utilities when prefers-reduced-motion.
--}}
<style id="webkernel-utility-reduced-motion">
@media (prefers-reduced-motion: reduce) {
    .transition-elevate,
    .elevate,
    .elevate-sm,
    .elevate-lg,
    .shadow-sm,
    .shadow,
    .shadow-md,
    .shadow-lg,
    .shadow-xl,
    .hover\:elevate,
    .hover\:elevate-sm,
    .hover\:elevate-lg,
    .hover\:shadow-sm,
    .hover\:shadow,
    .hover\:shadow-md,
    .hover\:shadow-lg,
    .hover\:shadow-xl,
    .hover\:scale-sm,
    .hover\:scale,
    .hover\:scale-lg,
    .active\:press,
    .hover\:appear,
    .hover\:appear.mobile\:appear {
        transition: none !important;
    }
    .elevate-sm,
    .elevate,
    .elevate-lg,
    .hover\:elevate-sm:hover,
    .hover\:elevate:hover,
    .hover\:elevate-lg:hover,
    .hover\:lift-sm:hover,
    .hover\:lift:hover,
    .hover\:lift-lg:hover,
    .hover\:scale-sm:hover,
    .hover\:scale:hover,
    .hover\:scale-lg:hover,
    .active\:press:active {
        transform: none !important;
    }
}
</style>
