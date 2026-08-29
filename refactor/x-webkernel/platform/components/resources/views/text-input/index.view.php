@props([
  'name' => '',
  'label' => null,
  'value' => '',
  'type' => 'text',
  'mode' => 'editable',
])
@if ($mode === 'readonly')
  <div class="wds-fo-field wds-readonly">
    <dt>{{ $label ?? $name }}</dt>
    <dd>{{ $value }}</dd>
  </div>
@else
  <p class="wds-fo-field">
    <label>
      {{ $label ?? $name }}
      <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
      />
    </label>
  </p>
@endif
