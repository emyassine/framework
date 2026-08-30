{{--
  utility-classes/_hover-border
  hover:primary-border, hover:success-600-border, …

  Filament .fi-section uses ring (box-shadow), not CSS border.
  hover:elevate rewrites box-shadow — so we set --wds-hover-border-color
  (composed into elevate) and paint ONE ring. No outline + black ring stack
  (that caused black → primary flash).

  Depends on: _tokens (and _elevate if used together)
--}}
@php
    $wds_border_palettes = ['danger', 'gray', 'info', 'primary', 'success', 'warning'];
    $wds_border_shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
@endphp
<style id="webkernel-utility-hover-border">
/*
 * Snap ring color (do not transition box-shadow/outline through gray).
 * Only transform is transitioned by elevate; ring color changes instantly.
 */
@media (hover: hover) {
@foreach ($wds_border_palettes as $palette)
    /*
     * Pre-bind color on the class itself so elevate's box-shadow can resolve
     * --wds-hover-border-color on the same frame as hover (no black intermediate).
     */
    .hover\:{{ $palette }}-border {
        --wds-hover-border-color: var(--{{ $palette }}-500);
    }
    .dark .hover\:{{ $palette }}-border,
    html.dark .hover\:{{ $palette }}-border,
    [data-theme="dark"] .hover\:{{ $palette }}-border,
    .fi-dark .hover\:{{ $palette }}-border {
        --wds-hover-border-color: var(--{{ $palette }}-400);
    }

    .hover\:{{ $palette }}-border:hover {
        --tw-ring-color: var(--wds-hover-border-color);
        --tw-ring-opacity: 1;
        border-color: var(--wds-hover-border-color);
    }

    /* Border only (no elevate): one 1px ring, keep existing soft shadow if any */
    .hover\:{{ $palette }}-border:hover:not(.hover\:elevate):not(.hover\:elevate-sm):not(.hover\:elevate-lg) {
        box-shadow:
            0 0 0 1px var(--wds-hover-border-color),
            var(--tw-shadow, 0 0 #0000);
    }

    @foreach ($wds_border_shades as $shade)
    .hover\:{{ $palette }}-{{ $shade }}-border {
        --wds-hover-border-color: var(--{{ $palette }}-{{ $shade }});
    }
    .hover\:{{ $palette }}-{{ $shade }}-border:hover {
        --tw-ring-color: var(--{{ $palette }}-{{ $shade }});
        --tw-ring-opacity: 1;
        border-color: var(--{{ $palette }}-{{ $shade }});
    }
    .hover\:{{ $palette }}-{{ $shade }}-border:hover:not(.hover\:elevate):not(.hover\:elevate-sm):not(.hover\:elevate-lg) {
        box-shadow:
            0 0 0 1px var(--{{ $palette }}-{{ $shade }}),
            var(--tw-shadow, 0 0 #0000);
    }
    @endforeach
@endforeach
}
</style>
