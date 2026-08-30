@props([
  'tab' => '',
  'active' => false,
  'icon' => null,
  'icon_position' => 'before',
  'badge' => null,
  'badge_color' => 'primary',
  'defer_badge' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $position = $icon_position instanceof \Webkernel\Platform\Components\IconPosition
    ? $icon_position->value
    : (string) $icon_position;
  $has_badge = $badge !== null && $badge !== '';
@endphp
<button
  type="button"
  {{ $attributes->class(['w-tabs-item', 'w-active' => $active])->merge([
    'id' => 'tab-'.$tab,
    'role' => 'tab',
    'aria-selected' => $active ? 'true' : 'false',
    'data-tab' => $tab,
  ]) }}
>
  @if (!empty($icon) && $position !== 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="w-tabs-item-label">{{ $slot }}</span>
  @if (!empty($icon) && $position === 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  @if ($has_badge && ! $defer_badge)
    <x-webkernel::badge :color="$badge_color" size="sm">{{ $badge }}</x-webkernel::badge>
  @elseif ($defer_badge)
    <span class="w-tabs-item-badge-placeholder">
      <x-webkernel::loading-indicator size="sm" />
    </span>
  @endif
</button>
