@props([
  'name' => '',
  'label' => null,
  'value' => '',
  'type' => 'text',
  'mode' => 'editable',
])
<style>
.wds-fo-field { margin: 0 0 0.75rem; color: var(--wds-text); }
.wds-fo-field label {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  font-size: var(--wds-text-sm);
  font-weight: 500;
  color: var(--wds-text);
}
.wds-fo-field input,
.wds-fo-field textarea,
.wds-fo-field select {
  font: inherit;
  color: var(--wds-text);
  background: var(--wds-surface);
  border: 1px solid var(--wds-border);
  border-radius: var(--wds-radius);
  padding: 0.4375rem 0.75rem;
}
.wds-fo-field input:focus,
.wds-fo-field textarea:focus,
.wds-fo-field select:focus {
  outline: 2px solid var(--primary-500);
  outline-offset: 1px;
  border-color: var(--primary-500);
}
.wds-fo-field.wds-readonly {
  display: grid;
  grid-template-columns: 8rem 1fr;
  gap: 0.5rem;
  font-size: var(--wds-text-sm);
}
.wds-fo-field.wds-readonly dt { color: var(--wds-text-muted); font-weight: 500; }
.wds-fo-field.wds-readonly dd { margin: 0; color: var(--wds-text); }
</style>
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
