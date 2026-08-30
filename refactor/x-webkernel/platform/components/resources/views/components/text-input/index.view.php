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
@if ($mode === 'readonly')
  <div class="w-fo-field w-readonly">
    <dt>{{ $label ?? $name }}</dt>
    <dd>{{ $value }}</dd>
  </div>
@else
  <p class="w-fo-field{{ $error ? ' w-invalid' : '' }}">
    <label>
      {{ $label ?? $name }}
      <span class="w-input-wrp">
        <span class="w-input-wrp-content">
          @if ($type === 'textarea')
            <textarea class="w-input" name="{{ $name }}" rows="5">{{ $value }}</textarea>
          @else
            <input class="w-input" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" />
          @endif
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
