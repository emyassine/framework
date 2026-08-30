{{--
  utility-classes/_focus
  Keyboard focus ring utility.
--}}
<style id="webkernel-utility-focus">
.focus\:ring:focus-visible {
    outline: 2px solid color-mix(in oklab, var(--primary-500, #3b82f6) 70%, transparent);
    outline-offset: 2px;
}
</style>
