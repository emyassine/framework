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
  $size = $size instanceof \Webkernel\Platform\Components\Size ? $size->value : (string) $size;
  $icon_position = $icon_position instanceof \Webkernel\Platform\Components\IconPosition
      ? $icon_position->value
      : (string) $icon_position;
  $tag = $tag === 'button' ? 'button' : 'a';
@endphp
@once('wds.link')
<style>
.wds-link {
  position: relative;
  display: inline-flex; align-items: center; justify-content: center;
  gap: 0.375rem; font-weight: 500;
  color: var(--wds-text-muted); outline: none;
}
.wds-link-label { align-self: baseline; }
.wds-link:hover, .wds-link:focus-visible { text-decoration: underline; color: var(--wds-text); }
.wds-link:focus-visible { border-radius: 2px; outline: 2px solid currentColor; outline-offset: 2px; }
.wds-link.wds-size-xs { gap: 0.25rem; font-size: var(--wds-text-xs); }
.wds-link.wds-size-sm { gap: 0.25rem; font-size: var(--wds-text-sm); }
.wds-link.wds-size-md, .wds-link.wds-size-lg, .wds-link.wds-size-xl { gap: 0.375rem; font-size: var(--wds-text-sm); }
.wds-link.wds-color-primary { color: var(--primary-700); }
.wds-link.wds-color-danger { color: var(--danger-700); }
.wds-link.wds-color-success { color: var(--success-700); }
.wds-link.wds-color-warning { color: var(--warning-700); }
.wds-link.wds-color-info { color: var(--info-700); }
.wds-link.wds-color-gray { color: var(--wds-text-muted); }
.wds-link.wds-disabled { opacity: 0.7; pointer-events: none; cursor: default; text-decoration: none; }
p > .wds-link, span > .wds-link { padding-bottom: 2px; vertical-align: middle; }
</style>
@endonce
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
    'wds-link',
    'wds-color-'.$color,
    'wds-size-'.$size,
    'wds-disabled' => $disabled,
  ]) }}
>
  @if ($icon && $icon_position !== 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="wds-link-label">{!! $slot !!}</span>
  @if ($icon && $icon_position === 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
</{{ $tag }}>
