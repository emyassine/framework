{{--
  <x-webkernel::select name="status">
    <option value="open">Open</option>
  </x-webkernel::select>
--}}
@props([
  'name' => '',
  'label' => null,
  'disabled' => false,
  'inline_prefix' => false,
  'options' => [],
  'value' => '',
  'error' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@if ($label)
<p {{ $attributes->class(['w-fo-field', 'w-invalid' => $error]) }}>
  <label>
    {{ $label }}
@endif
<select
  name="{{ $name }}"
  @if ($disabled) disabled @endif
  class="w-select-input{{ $inline_prefix ? ' w-select-input-has-inline-prefix' : '' }}"
>
  @if (\is_array($options) && $options !== [])
    @foreach ($options as $opt_value => $opt_label)
      <option value="{{ $opt_value }}" @if ((string) $opt_value === (string) $value) selected @endif>{{ $opt_label }}</option>
    @endforeach
  @else
    {!! $slot !!}
  @endif
</select>
@if ($label)
  </label>
  @if ($error)
    <span class="w-field-error">{{ $error }}</span>
  @endif
</p>
@endif
