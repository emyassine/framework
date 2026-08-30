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
@once('wds.badge')
<style>
.wds-badge {
  display: inline-flex; align-items: center; justify-content: center;
  gap: 0.25rem; min-width: 1.5rem;
  padding: 0.125rem 0.5rem; border-radius: var(--wds-radius);
  font-size: var(--wds-text-xs); font-weight: 500;
  background: var(--gray-50); color: var(--gray-600);
  box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--gray-600) 10%, transparent);
  max-width: 100%;
}
.wds-badge-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.wds-badge.wds-size-xs { min-width: 1rem; padding: 0 0.125rem; letter-spacing: -0.04em; }
.wds-badge.wds-size-sm { min-width: 1.25rem; padding: 0.125rem 0.375rem; }
.wds-badge.wds-color-primary { background: var(--primary-50); color: var(--primary-700); box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--primary-600) 10%, transparent); }
.wds-badge.wds-color-success { background: var(--success-50); color: var(--success-700); box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--success-600) 10%, transparent); }
.wds-badge.wds-color-warning { background: var(--warning-50); color: var(--warning-700); box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--warning-600) 10%, transparent); }
.wds-badge.wds-color-danger { background: var(--danger-50); color: var(--danger-700); box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--danger-600) 10%, transparent); }
.wds-badge.wds-color-info { background: var(--info-50); color: var(--info-700); box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--info-600) 10%, transparent); }
.wds-badge.wds-color-gray { background: var(--wds-bg-subtle); color: var(--wds-text-muted); }
.wds-badge.wds-disabled { opacity: 0.7; pointer-events: none; }
a.wds-badge { text-decoration: none; color: inherit; }
</style>
@endonce
<{{ $tag }}
  @if ($tag === 'a' && $href && ! $disabled)
    href="{{ $href }}"
  @endif
  {{ $attributes->class([
    'wds-badge',
    'wds-color-'.$color,
    'wds-size-'.$size,
    'wds-disabled' => $disabled,
  ]) }}
>
  @if ($icon && $icon_position !== 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="wds-badge-label">{!! $slot !!}</span>
  @if ($icon && $icon_position === 'after')
    <x-webkernel::icon :name="$icon" />
  @endif
</{{ $tag }}>
