{{--
  <x-webkernel::loading-indicator />
--}}
@props([
  'size' => 'md',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $size = $size instanceof \Webkernel\Platform\Components\Enums\Size ? $size->value : (string) $size;
@endphp
<svg
  {{ $attributes->class(['w-loading-indicator', 'w-size-'.$size])->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'aria-hidden' => 'true']) }}
>
  <path
    d="M12 3a9 9 0 1 0 9 9"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
  />
</svg>
