{{--
  <x-webkernel::link href="/invoices" icon="arrow-right">Open</x-webkernel::link>
--}}
@props([
  'href' => null,
  'color' => 'primary',
  'size' => 'md',
  'icon' => null,
  'icon_position' => 'before',
  'tag' => 'a',
  'target' => null,
  'disabled' => false,
  'tooltip' => null,
  'weight' => 'medium',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $size = $size instanceof \Webkernel\Platform\Components\Enums\Size ? $size->value : (string) $size;
  $icon_position = $icon_position instanceof \Webkernel\Platform\Components\Enums\IconPosition
      ? $icon_position->value
      : (string) $icon_position;
  $tag = $tag === 'button' ? 'button' : 'a';
@endphp
<{{ $tag }}
  @if ($tag === 'a' && $href && ! $disabled)
    href="{{ $href }}"
    @if ($target)
      target="{{ $target }}"
    @endif
  @endif
  @if ($tag === 'button') type="button" @endif
  @if ($disabled) aria-disabled="true" @endif
  @if ($tooltip) title="{{ $tooltip }}" @endif
  {{ $attributes->class([
    'w-link',
    'w-color-'.$color,
    'w-size-'.$size,
    'w-disabled' => $disabled,
  ]) }}
>
  @if ($icon && $icon_position !== 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="w-link-label">{!! $slot !!}</span>
  @if ($icon && $icon_position === 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
</{{ $tag }}>
