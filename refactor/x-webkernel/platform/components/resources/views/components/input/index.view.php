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
<input
  type="{{ $type }}"
  name="{{ $name }}"
  value="{{ $value }}"
  @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
  @if ($disabled) disabled @endif
  {{ $attributes->class([
    'w-input',
    'w-input-has-inline-prefix' => $inline_prefix,
    'w-input-has-inline-suffix' => $inline_suffix,
  ]) }}
/>
