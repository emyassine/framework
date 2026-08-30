{{--
  <x-webkernel::icon-button icon="plus" label="Create" />
--}}
@props([
  'icon' => null,
  'label' => null,
  'color' => 'gray',
  'size' => 'md',
  'href' => null,
  'tag' => 'button',
  'type' => 'button',
  'disabled' => false,
  'tooltip' => null,
  'badge' => null,
  'badge_color' => 'primary',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $size = $size instanceof \Webkernel\Platform\Components\Size ? $size->value : (string) $size;
  $tag = $tag === 'a' || ($href !== null && $href !== '') ? 'a' : 'button';
  $title = $tooltip ?? $label;
@endphp
<{{ $tag }}
  @if ($tag === 'a' && $href && ! $disabled)
    href="{{ $href }}"
  @endif
  @if ($tag === 'button')
    type="{{ $type }}"
  @endif
  @if ($disabled) disabled aria-disabled="true" @endif
  @if ($title) title="{{ $title }}" @endif
  @if ($label) aria-label="{{ $label }}" @endif
  {{ $attributes->class([
    'w-icon-btn',
    'w-color-'.$color,
    'w-size-'.$size,
    'w-disabled' => $disabled,
  ]) }}
>
  @if ($icon)
    <x-webkernel::icon :name="$icon" />
  @endif
  {!! $slot !!}
  @if ($badge !== null && $badge !== '')
    <span class="w-icon-btn-badge">
      <x-webkernel::badge :color="$badge_color" size="xs">{{ $badge }}</x-webkernel::badge>
    </span>
  @endif
</{{ $tag }}>
