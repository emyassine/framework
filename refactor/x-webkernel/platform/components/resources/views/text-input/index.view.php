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
@once('wds.field')
<style>
.wds-fo-field { margin: 0 0 0.75rem; color: var(--wds-text); }
.wds-fo-field > label,
.wds-fo-field > span {
  display: flex; flex-direction: column; gap: 0.375rem;
  font-size: var(--wds-text-sm); font-weight: 500; color: var(--wds-text);
}
.wds-fo-field.wds-readonly {
  display: grid; grid-template-columns: 8rem 1fr; gap: 0.5rem;
  font-size: var(--wds-text-sm);
}
.wds-fo-field.wds-readonly dt { color: var(--wds-text-muted); font-weight: 500; }
.wds-fo-field.wds-readonly dd { margin: 0; color: var(--wds-text); }
@media (max-width: 640px) {
  .wds-fo-field.wds-readonly { grid-template-columns: 1fr; }
}
</style>
@endonce
@if ($mode === 'readonly')
  <div class="wds-fo-field wds-readonly">
    <dt>{{ $label ?? $name }}</dt>
    <dd>{{ $value }}</dd>
  </div>
@else
  <p class="wds-fo-field">
    <label>
      {{ $label ?? $name }}
      <span class="wds-input-wrp">
        <span class="wds-input-wrp-content">
          <input class="wds-input" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" />
        </span>
      </span>
    </label>
  </p>
@endif
