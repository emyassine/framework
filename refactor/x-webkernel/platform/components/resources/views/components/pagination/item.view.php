@props([
  'active' => false,
  'disabled' => false,
  'label' => null,
  'icon' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.pagination.item')
<style>
.wds-pagination-item-btn:disabled { pointer-events: none; }
</style>
@endonce
<li {{ $attributes->class([
  'wds-pagination-item',
  'wds-disabled' => $disabled,
  'wds-active' => $active,
]) }}>
  <button
    type="button"
    class="wds-pagination-item-btn"
    @if ($active) aria-current="page" @endif
    @if ($disabled) disabled aria-hidden="true" @endif
  >
    @if ($icon)
      <x-webkernel::icon :name="$icon" />
    @endif
    @if ($label !== null && $label !== '')
      <span class="wds-pagination-item-label">{{ $label }}</span>
    @endif
  </button>
</li>
