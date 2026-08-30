{{--
  <x-webkernel::badge color="success">Paid</x-webkernel::badge>
--}}
@props([
  'color' => 'primary',
  'size' => 'md',
  'icon' => null,
  'icon_position' => 'before',
  'href' => null,
  'tag' => 'span',
  'disabled' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $size = $size instanceof \Webkernel\Platform\Components\Size ? $size->value : (string) $size;
  $tag = $tag === 'a' || ($href !== null && $href !== '') ? 'a' : (string) $tag;
@endphp
<{{ $tag }}
  @if ($tag === 'a' && $href && ! $disabled)
    href="{{ $href }}"
  @endif
  {{ $attributes->class([
    'w-badge',
    'w-color-'.$color,
    'w-size-'.$size,
    'w-disabled' => $disabled,
  ]) }}
>
  @if ($icon && $icon_position !== 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="w-badge-label">{!! $slot !!}</span>
  @if ($icon && $icon_position === 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
</{{ $tag }}>
