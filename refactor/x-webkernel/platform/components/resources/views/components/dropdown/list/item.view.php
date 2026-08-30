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
<{{ $tag }}
  @if ($tag === 'a' && $href && ! $disabled)
    href="{{ $href }}"
  @endif
  @if ($tag === 'button') type="button" @endif
  @if ($disabled) disabled aria-disabled="true" @endif
  role="menuitem"
  {{ $attributes->class([
    'w-dropdown-list-item',
    'w-color-'.$color => $color !== 'gray',
    'w-disabled' => $disabled,
    'w-selected' => $selected,
  ]) }}
>
  @if ($icon)
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="w-dropdown-list-item-label">{!! $slot !!}</span>
  @if ($badge !== null && $badge !== '')
    <x-webkernel::badge :color="$badge_color" size="sm">{{ $badge }}</x-webkernel::badge>
  @endif
</{{ $tag }}>
