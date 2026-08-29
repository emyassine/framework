{{--
  <x-webkernel::button color="primary" size="md">Save</x-webkernel::button>
  <x-webkernel::button href="/invoices" tag="a">Open</x-webkernel::button>
  <x-webkernel::button icon="plus" outlined>New</x-webkernel::button>
--}}
@props([
  'badge' => null,
  'badge_color' => 'primary',
  'color' => 'primary',
  'disabled' => false,
  'form' => null,
  'href' => null,
  'icon' => null,
  'icon_position' => 'before',
  'outlined' => false,
  'size' => 'md',
  'tag' => 'button',
  'target' => null,
  'tooltip' => null,
  'type' => 'button',
])
@php
  $tag = $tag === 'a' || ($href !== null && $href !== '') ? 'a' : 'button';
  $size = $size instanceof \Webkernel\Platform\Components\Size ? $size->value : (string) $size;
  $icon_position = $icon_position instanceof \Webkernel\Platform\Components\IconPosition
      ? $icon_position->value
      : (string) $icon_position;
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<{{ $tag }}
  @if ($tag === 'a' && $href && ! $disabled)
    href="{{ $href }}"
    @if ($target)
      target="{{ $target }}"
      @if ($target === '_blank') rel="noopener noreferrer" @endif
    @endif
  @endif
  @if ($tag === 'button')
    type="{{ $type }}"
    @if ($form) form="{{ $form }}" @endif
    @if ($disabled) disabled @endif
  @endif
  @if ($disabled) aria-disabled="true" @endif
  @if ($tooltip) title="{{ $tooltip }}" @endif
  {{ $attributes->class([
    'wds-btn',
    'wds-color-'.$color,
    'wds-size-'.$size,
    'wds-outlined' => $outlined,
    'wds-disabled' => $disabled,
  ]) }}
>
  @if ($icon && $icon_position !== 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  @if ($slot !== '')
    <span class="wds-btn-label">{!! $slot !!}</span>
  @endif
  @if ($icon && $icon_position === 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  @if ($badge !== null && $badge !== '')
    <span class="wds-btn-badge wds-badge wds-color-{{ $badge_color }}">{!! $badge !!}</span>
  @endif
</{{ $tag }}>
