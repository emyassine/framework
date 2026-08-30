{{--
  utility-classes/_hover-bg
  ─────────────────────────
  Hover background utilities from Filament palette CSS vars.

  Usage:
    class="hover:background-gray"   full name
    class="hover:bg-gray"           alias
    class="hover:bg-primary"
    class="hover:bg-primary-600"
    class="hover:background-success-100"

  Semantic (no shade): light --{palette}-100 / dark --{palette}-800
  Shade-specific:      var(--{palette}-{shade}) as-is

  Depends on: palette vars (--primary-*, --gray-*, …)
--}}
@php
    $wds_bg_palettes = ['danger', 'gray', 'info', 'primary', 'success', 'warning'];
    $wds_bg_shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
@endphp
<style id="webkernel-utility-hover-bg">
@media (hover: hover) {
@foreach ($wds_bg_palettes as $palette)
    /* hover:background-{{ $palette }}  |  hover:bg-{{ $palette }} */
    .hover\:background-{{ $palette }},
    .hover\:bg-{{ $palette }} {
        transition-property: background-color, color, border-color, box-shadow, transform, filter, opacity;
        transition-duration: var(--wds-utility-duration, 0.2s);
        transition-timing-function: var(--wds-utility-ease, cubic-bezier(0.4, 0, 0.2, 1));
    }
    .hover\:background-{{ $palette }}:hover,
    .hover\:bg-{{ $palette }}:hover {
        background-color: var(--{{ $palette }}-100);
    }
    .dark .hover\:background-{{ $palette }}:hover,
    html.dark .hover\:background-{{ $palette }}:hover,
    [data-theme="dark"] .hover\:background-{{ $palette }}:hover,
    .fi-dark .hover\:background-{{ $palette }}:hover,
    .dark .hover\:bg-{{ $palette }}:hover,
    html.dark .hover\:bg-{{ $palette }}:hover,
    [data-theme="dark"] .hover\:bg-{{ $palette }}:hover,
    .fi-dark .hover\:bg-{{ $palette }}:hover {
        background-color: var(--{{ $palette }}-800);
    }

    @foreach ($wds_bg_shades as $shade)
    /* hover:background-{{ $palette }}-{{ $shade }}  |  hover:bg-{{ $palette }}-{{ $shade }} */
    .hover\:background-{{ $palette }}-{{ $shade }},
    .hover\:bg-{{ $palette }}-{{ $shade }} {
        transition-property: background-color, color, border-color, box-shadow, transform, filter, opacity;
        transition-duration: var(--wds-utility-duration, 0.2s);
        transition-timing-function: var(--wds-utility-ease, cubic-bezier(0.4, 0, 0.2, 1));
    }
    .hover\:background-{{ $palette }}-{{ $shade }}:hover,
    .hover\:bg-{{ $palette }}-{{ $shade }}:hover {
        background-color: var(--{{ $palette }}-{{ $shade }});
    }
    @endforeach
@endforeach
}
</style>
