@props([
  'icon' => null,
  'color' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.dropdown.header')
<style>
.wds-dropdown-header {
  display: flex; width: 100%; gap: 0.5rem; padding: 0.75rem;
  font-size: var(--wds-text-sm); font-weight: 500; color: var(--wds-text);
}
.wds-dropdown-header .wds-icon { color: var(--wds-text-faint); }
.wds-dropdown-header > span { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: start; }
</style>
@endonce
<div {{ $attributes->class('wds-dropdown-header') }}>
  @if ($icon)
    <x-webkernel::icon :name="$icon" />
  @endif
  <span>{!! $slot !!}</span>
</div>
