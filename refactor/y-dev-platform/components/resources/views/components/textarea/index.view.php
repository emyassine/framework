{{--
  Labeled multiline field. Matches Filament textarea: wrapper + autosize box + textarea.
--}}
@props([
  'name' => '',
  'label' => null,
  'value' => '',
  'rows' => 2,
  'cols' => null,
  'autosize' => false,
  'placeholder' => null,
  'disabled' => false,
  'mode' => 'editable',
  'hint' => null,
  'error' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $rows = \is_numeric($rows) ? (int) $rows : 2;
  if ($rows < 1) {
    $rows = 2;
  }
@endphp
@if ($mode === 'readonly')
  <div {{ $attributes->class(['w-fo-field', 'w-readonly']) }}>
    <dt>{{ $label ?? $name }}</dt>
    <dd>{{ $value }}</dd>
  </div>
@else
  <p {{ $attributes->class(['w-fo-field', 'w-fo-textarea-wrp', 'w-invalid' => $error]) }}>
    <label>
      {{ $label ?? $name }}
      <span class="w-input-wrp{{ $disabled ? ' w-disabled' : '' }}{{ $error ? ' w-invalid' : '' }}">
        <span class="w-input-wrp-content">
          <span class="w-fo-textarea{{ $autosize ? ' w-autosizable' : '' }}" style="--w-rows: {{ $rows }}" data-w-textarea>
            <textarea
              class="w-input"
              name="{{ $name }}"
              rows="{{ $rows }}"
              @if ($cols) cols="{{ $cols }}" @endif
              @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
              @if ($disabled) disabled @endif
              @if ($autosize) data-autosize="true" @endif
            >{{ $value }}</textarea>
          </span>
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
