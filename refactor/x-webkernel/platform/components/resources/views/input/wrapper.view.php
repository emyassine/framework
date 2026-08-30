{{--
  <x-webkernel::input.wrapper prefix_icon="search">
    <x-webkernel::input name="q" />
  </x-webkernel::input.wrapper>
--}}
@props([
  'disabled' => false,
  'valid' => true,
  'prefix' => null,
  'prefix_icon' => null,
  'suffix' => null,
  'suffix_icon' => null,
  'inline_prefix' => false,
  'inline_suffix' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $has_prefix = ($prefix !== null && $prefix !== '') || ($prefix_icon !== null && $prefix_icon !== '');
  $has_suffix = ($suffix !== null && $suffix !== '') || ($suffix_icon !== null && $suffix_icon !== '');
@endphp
@once('wds.input-wrp')
<style>
.wds-input-wrp {
  display: flex; border-radius: var(--wds-radius-lg);
  background: var(--wds-surface);
  box-shadow: 0 1px 2px color-mix(in srgb, var(--wds-text) 6%, transparent), 0 0 0 1px color-mix(in srgb, var(--wds-text) 10%, transparent);
  transition: box-shadow var(--wds-transition);
}
.wds-input-wrp:not(.wds-disabled):focus-within {
  box-shadow: 0 0 0 2px var(--primary-600);
}
.wds-input-wrp.wds-invalid { box-shadow: 0 0 0 1px var(--danger-600); }
.wds-input-wrp.wds-invalid:not(.wds-disabled):focus-within { box-shadow: 0 0 0 2px var(--danger-600); }
.wds-input-wrp.wds-disabled { background: var(--wds-bg-subtle); }
.wds-input-wrp-prefix, .wds-input-wrp-suffix {
  display: flex; align-items: center; gap: 0.75rem; color: var(--wds-text-faint);
}
.wds-input-wrp-prefix { padding-inline-start: 0.75rem; }
.wds-input-wrp-prefix.wds-inline { padding-inline-end: 0.5rem; }
.wds-input-wrp-prefix:not(.wds-inline) {
  border-inline-end: 1px solid var(--wds-border); padding-inline-end: 0.75rem;
}
.wds-input-wrp-suffix { padding-inline-end: 0.75rem; }
.wds-input-wrp-suffix.wds-inline { padding-inline-start: 0.5rem; }
.wds-input-wrp-suffix:not(.wds-inline) {
  border-inline-start: 1px solid var(--wds-border); padding-inline-start: 0.75rem;
}
.wds-input-wrp-content { min-width: 0; flex: 1; }
.wds-input-wrp-label { font-size: var(--wds-text-sm); white-space: nowrap; color: var(--wds-text-muted); }
</style>
@endonce
<div {{ $attributes->class([
  'wds-input-wrp',
  'wds-disabled' => $disabled,
  'wds-invalid' => ! $valid,
]) }}>
  @if ($has_prefix)
    <div class="wds-input-wrp-prefix{{ $inline_prefix ? ' wds-inline' : '' }}">
      @if ($prefix_icon)
        <x-webkernel::icon :name="$prefix_icon" />
      @endif
      @if ($prefix !== null && $prefix !== '')
        <span class="wds-input-wrp-label">{{ $prefix }}</span>
      @endif
    </div>
  @endif
  <div class="wds-input-wrp-content">{!! $slot !!}</div>
  @if ($has_suffix)
    <div class="wds-input-wrp-suffix{{ $inline_suffix ? ' wds-inline' : '' }}">
      @if ($suffix !== null && $suffix !== '')
        <span class="wds-input-wrp-label">{{ $suffix }}</span>
      @endif
      @if ($suffix_icon)
        <x-webkernel::icon :name="$suffix_icon" />
      @endif
    </div>
  @endif
</div>
