{{--
  Session flash stack. Matches Filament notifications container.
--}}
@props([
  'alignment' => 'end',
  'vertical_alignment' => 'start',
])
<div
  class="w-no w-align-{{ $alignment }} w-vertical-align-{{ $vertical_alignment }}"
  role="status"
  aria-atomic="false"
>
  {!! $slot !!}
</div>
