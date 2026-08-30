{{--
  utility-classes/_tokens
  Motion + elevation shadow tokens (no 1px edge ring in *-lift variants).
  Load this before elevate / hover-border / shadow utilities.
--}}
<style id="webkernel-utility-tokens">
:root {
    --wds-elevate-y-sm: -1px;
    --wds-elevate-y: -2px;
    --wds-elevate-y-lg: -4px;
    --wds-utility-ease: cubic-bezier(0.4, 0, 0.2, 1);
    --wds-utility-duration: 0.2s;

    /* Full shadows (soft + subtle 1px edge) — always-on .shadow-* */
    --wds-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.06), 0 0 0 1px rgb(0 0 0 / 0.04);
    --wds-shadow: 0 2px 6px -1px rgb(0 0 0 / 0.08), 0 0 0 1px rgb(0 0 0 / 0.05);
    --wds-shadow-md: 0 6px 14px -4px rgb(0 0 0 / 0.12), 0 0 0 1px rgb(0 0 0 / 0.05);
    --wds-shadow-lg: 0 12px 28px -8px rgb(0 0 0 / 0.16), 0 0 0 1px rgb(0 0 0 / 0.06);
    --wds-shadow-xl: 0 20px 40px -12px rgb(0 0 0 / 0.22), 0 0 0 1px rgb(0 0 0 / 0.07);

    /* Lift-only (no edge ring) — composed with a single ring layer on hover */
    --wds-shadow-sm-lift: 0 1px 2px 0 rgb(0 0 0 / 0.06);
    --wds-shadow-lift: 0 2px 6px -1px rgb(0 0 0 / 0.08);
    --wds-shadow-md-lift: 0 6px 14px -4px rgb(0 0 0 / 0.12);
    --wds-shadow-lg-lift: 0 12px 28px -8px rgb(0 0 0 / 0.16);
    --wds-shadow-xl-lift: 0 20px 40px -12px rgb(0 0 0 / 0.22);

    /* Default edge when no hover:*-border utility (matches old shadow 1px) */
    --wds-elevate-ring-default: rgb(0 0 0 / 0.05);
}

.dark,
html.dark,
[data-theme="dark"],
.fi-dark {
    --wds-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.35), 0 0 0 1px rgb(255 255 255 / 0.06);
    --wds-shadow: 0 2px 6px -1px rgb(0 0 0 / 0.45), 0 0 0 1px rgb(255 255 255 / 0.07);
    --wds-shadow-md: 0 6px 14px -4px rgb(0 0 0 / 0.5), 0 0 0 1px rgb(255 255 255 / 0.08);
    --wds-shadow-lg: 0 12px 28px -8px rgb(0 0 0 / 0.55), 0 0 0 1px rgb(255 255 255 / 0.09);
    --wds-shadow-xl: 0 20px 40px -12px rgb(0 0 0 / 0.65), 0 0 0 1px rgb(255 255 255 / 0.1);

    --wds-shadow-sm-lift: 0 1px 2px 0 rgb(0 0 0 / 0.35);
    --wds-shadow-lift: 0 2px 6px -1px rgb(0 0 0 / 0.45);
    --wds-shadow-md-lift: 0 6px 14px -4px rgb(0 0 0 / 0.5);
    --wds-shadow-lg-lift: 0 12px 28px -8px rgb(0 0 0 / 0.55);
    --wds-shadow-xl-lift: 0 20px 40px -12px rgb(0 0 0 / 0.65);

    --wds-elevate-ring-default: rgb(255 255 255 / 0.08);
}
</style>
