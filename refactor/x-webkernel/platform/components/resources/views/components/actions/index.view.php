{{--
  <x-webkernel::actions alignment="end">…</x-webkernel::actions>
--}}
@props([
  'full_width' => false,
  'alignment' => 'start',
  'vertical_alignment' => '',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $align = \is_string($alignment) && $alignment !== '' ? $alignment : 'start';
  $valign = \is_string($vertical_alignment) ? $vertical_alignment : '';
@endphp
<div {{ $attributes->class([
  'w-actions',
  'w-full' => $full_width,
  'w-align-'.$align,
  'w-valign-'.$valign => $valign !== '',
]) }}>
  {!! $slot !!}
</div>
