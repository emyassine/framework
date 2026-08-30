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
@once('wds.icon-btn')
<style>
.wds-icon-btn {
  position: relative;
  display: inline-flex; align-items: center; justify-content: center;
  width: 2.25rem; height: 2.25rem;
  border-radius: var(--wds-radius-lg);
  color: var(--wds-text-muted);
  background: transparent;
  transition: color var(--wds-transition), background var(--wds-transition);
  outline: none;
}
.wds-icon-btn:hover { color: var(--wds-text); background: var(--wds-bg-subtle); }
.wds-icon-btn:focus-visible { box-shadow: 0 0 0 2px var(--primary-600); }
.wds-icon-btn svg, .wds-icon-btn .wds-icon { width: 1rem; height: 1rem; }
.wds-icon-btn.wds-size-xs { width: 1.75rem; height: 1.75rem; }
.wds-icon-btn.wds-size-sm { width: 2rem; height: 2rem; }
.wds-icon-btn.wds-size-lg { width: 2.5rem; height: 2.5rem; }
.wds-icon-btn.wds-size-xl { width: 2.75rem; height: 2.75rem; }
.wds-icon-btn.wds-color-primary { color: var(--primary-600); }
.wds-icon-btn.wds-color-danger { color: var(--danger-600); }
.wds-icon-btn.wds-color-success { color: var(--success-600); }
.wds-icon-btn.wds-color-warning { color: var(--warning-700); }
.wds-icon-btn.wds-color-info { color: var(--info-600); }
.wds-icon-btn.wds-disabled, .wds-icon-btn[disabled] { opacity: 0.7; pointer-events: none; cursor: default; }
.wds-icon-btn-badge {
  position: absolute; inset-inline-start: 100%; top: 0;
  transform: translate(-50%, -50%); z-index: 1;
}
[dir="rtl"] .wds-icon-btn-badge { transform: translate(50%, -50%); }
a.wds-icon-btn { text-decoration: none; color: inherit; }
</style>
@endonce
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
    'wds-icon-btn',
    'wds-color-'.$color,
    'wds-size-'.$size,
    'wds-disabled' => $disabled,
  ]) }}
>
  @if ($icon)
    <x-webkernel::icon :name="$icon" />
  @endif
  {!! $slot !!}
  @if ($badge !== null && $badge !== '')
    <span class="wds-icon-btn-badge">
      <x-webkernel::badge :color="$badge_color" size="xs">{{ $badge }}</x-webkernel::badge>
    </span>
  @endif
</{{ $tag }}>
