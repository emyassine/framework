{{--
  Labeled field. The bare control is <x-webkernel::input> inside <x-webkernel::input.wrapper>.
--}}
@props([
  'name' => '',
  'label' => null,
  'value' => '',
  'type' => 'text',
  'mode' => 'editable',
  'hint' => null,
  'error' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@if ($mode === 'readonly')
  <div {{ $attributes->class(['w-fo-field', 'w-readonly']) }}>
    <dt>{{ $label ?? $name }}</dt>
    <dd>{{ $value }}</dd>
  </div>
@else
  <p {{ $attributes->class(['w-fo-field', 'w-invalid' => $error]) }}>
    <label>
      {{ $label ?? $name }}
      <span class="w-input-wrp{{ $error ? ' w-invalid' : '' }}">
        <span class="w-input-wrp-content">
          <input class="w-input" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" />
        </span>
      </span>
    </label>
    @if ($error)
      <span class="w-field-error">{{ $error }}</span>
    @elseif ($hint)
      <span class="w-form-hint">{{ $hint }}</span>
    @endif
  </p>
@endif
