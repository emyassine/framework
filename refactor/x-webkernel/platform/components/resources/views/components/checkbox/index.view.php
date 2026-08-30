{{--
  <x-webkernel::checkbox name="agree" :checked="true" label="I agree" />
--}}
@props([
  'name' => '',
  'value' => '1',
  'checked' => false,
  'disabled' => false,
  'valid' => true,
  'label' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $slot = $slot ?? '';
@endphp
<label {{ $attributes->class(['w-checkbox-wrap']) }}>
  <input
    type="checkbox"
    name="{{ $name }}"
    value="{{ $value }}"
    @if ($checked) checked @endif
    @if ($disabled) disabled @endif
    class="w-checkbox-input{{ $valid ? '' : ' w-invalid' }}"
  />
  @if ($label !== null && $label !== '')
    <span>{{ $label }}</span>
  @endif
  {!! $slot !!}
</label>
