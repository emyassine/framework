{{--
  Labeled field. The bare control is <x-webkernel::input> inside <x-webkernel::input.wrapper>.
--}}
@props([
  'name' => '',
  'label' => null,
  'value' => '',
  'type' => 'text',
  'mode' => 'editable',
])
@if ($mode === 'readonly')
  <div class="w-fo-field w-readonly">
    <dt>{{ $label ?? $name }}</dt>
    <dd>{{ $value }}</dd>
  </div>
@else
  <p class="w-fo-field">
    <label>
      {{ $label ?? $name }}
      <span class="w-input-wrp">
        <span class="w-input-wrp-content">
          <input class="w-input" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" />
        </span>
      </span>
    </label>
  </p>
@endif
