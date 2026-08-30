{{--
  <x-webkernel::input name="email" type="email" />
--}}
@props([
  'name' => '',
  'type' => 'text',
  'value' => '',
  'placeholder' => null,
  'disabled' => false,
  'inline_prefix' => false,
  'inline_suffix' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.input')
<style>
input.wds-input {
  display: block; width: 100%; appearance: none;
  border: none; background: transparent;
  padding: 0.375rem 0.75rem;
  font: inherit; font-size: var(--wds-text-sm); line-height: 1.5;
  color: var(--wds-text); text-align: start;
  transition: color var(--wds-transition);
  outline: none;
}
input.wds-input::placeholder { color: var(--wds-text-faint); }
input.wds-input:disabled { color: var(--wds-text-muted); }
input.wds-input.wds-input-has-inline-prefix { padding-inline-start: 0; }
input.wds-input.wds-input-has-inline-suffix { padding-inline-end: 0; }
input.wds-input.wds-align-center { text-align: center; }
input.wds-input.wds-align-end { text-align: end; }
@supports (-webkit-touch-callout: none) {
  input.wds-input { font-size: 1rem; }
}
input[type='date'].wds-input,
input[type='datetime-local'].wds-input,
input[type='time'].wds-input {
  background: color-mix(in srgb, var(--color-white) 1%, transparent);
}
</style>
@endonce
<input
  type="{{ $type }}"
  name="{{ $name }}"
  value="{{ $value }}"
  @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
  @if ($disabled) disabled @endif
  {{ $attributes->class([
    'wds-input',
    'wds-input-has-inline-prefix' => $inline_prefix,
    'wds-input-has-inline-suffix' => $inline_suffix,
  ]) }}
/>
