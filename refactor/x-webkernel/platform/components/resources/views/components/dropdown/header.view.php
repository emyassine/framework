@props([
  'icon' => null,
  'color' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div {{ $attributes->class('w-dropdown-header') }}>
  @if ($icon)
    <x-webkernel::icon :name="$icon" />
  @endif
  <span>{!! $slot !!}</span>
</div>
