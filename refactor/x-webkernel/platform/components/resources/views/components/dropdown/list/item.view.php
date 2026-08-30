{{--
  <x-webkernel::dropdown.list.item href="/edit" icon="pencil">Edit</x-webkernel::dropdown.list.item>
--}}
@props([
  'href' => null,
  'icon' => null,
  'color' => 'gray',
  'tag' => 'button',
  'disabled' => false,
  'selected' => false,
  'badge' => null,
  'badge_color' => 'primary',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $tag = $tag === 'a' || ($href !== null && $href !== '') ? 'a' : 'button';
@endphp
@once('wds.dropdown.list.item')
<style>
.wds-dropdown-list-item {
  display: flex; width: 100%; align-items: center; gap: 0.5rem;
  overflow: hidden; border-radius: var(--wds-radius);
  padding: 0.5rem; font-size: var(--wds-text-sm); white-space: nowrap;
  color: var(--wds-text); text-align: start; outline: none; user-select: none;
}
.wds-dropdown-list-item:not(.wds-disabled):hover,
.wds-dropdown-list-item:not(.wds-disabled):focus-visible,
.wds-dropdown-list-item.wds-selected { background: var(--wds-bg-subtle); }
.wds-dropdown-list-item.wds-disabled { opacity: 0.7; pointer-events: none; cursor: default; }
.wds-dropdown-list-item > .wds-icon { color: var(--wds-text-faint); }
.wds-dropdown-list-item-label { flex: 1; overflow: hidden; text-overflow: ellipsis; text-align: start; color: var(--wds-text); }
.wds-dropdown-list-item.wds-color-danger { color: var(--danger-700); }
.wds-dropdown-list-item.wds-color-danger > .wds-icon { color: var(--danger-500); }
.wds-dropdown-list-item.wds-color-danger:not(.wds-disabled):hover { background: var(--danger-50); }
a.wds-dropdown-list-item { text-decoration: none; color: inherit; }
</style>
@endonce
<{{ $tag }}
  @if ($tag === 'a' && $href && ! $disabled)
    href="{{ $href }}"
  @endif
  @if ($tag === 'button') type="button" @endif
  @if ($disabled) disabled aria-disabled="true" @endif
  role="menuitem"
  {{ $attributes->class([
    'wds-dropdown-list-item',
    'wds-color-'.$color => $color !== 'gray',
    'wds-disabled' => $disabled,
    'wds-selected' => $selected,
  ]) }}
>
  @if ($icon)
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="wds-dropdown-list-item-label">{!! $slot !!}</span>
  @if ($badge !== null && $badge !== '')
    <x-webkernel::badge :color="$badge_color" size="sm">{{ $badge }}</x-webkernel::badge>
  @endif
</{{ $tag }}>
