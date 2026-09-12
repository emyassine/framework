@props([
  'active' => false,
  'disabled' => false,
  'label' => null,
  'icon' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<li {{ $attributes->class([
  'w-pagination-item',
  'w-disabled' => $disabled,
  'w-active' => $active,
]) }}>
  <button
    type="button"
    class="w-pagination-item-btn"
    @if ($active) aria-current="page" @endif
    @if ($disabled) disabled aria-hidden="true" @endif
  >
    @if ($icon)
      <x-webkernel::icon :name="$icon" />
    @endif
    @if ($label !== null && $label !== '')
      <span class="w-pagination-item-label">{{ $label }}</span>
    @endif
  </button>
</li>
