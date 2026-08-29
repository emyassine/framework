@props([
  'name' => '',
  'checked' => false,
  'label' => null,
])
<style>
.wds-toggle-wrap { display: inline-flex; align-items: center; gap: 0.6em; color: var(--wds-text); }
.wds-toggle {
  width: 2.6em; height: 1.45em; border-radius: 999px; background: var(--wds-border-strong);
  position: relative; flex: 0 0 auto;
}
.wds-toggle[aria-checked="true"] { background: var(--primary-600); }
.wds-toggle i {
  position: absolute; top: 2px; inset-inline-start: 2px;
  width: 1.05em; height: 1.05em; border-radius: 999px; background: var(--color-white);
  transition: inset-inline-start 0.15s ease;
}
.wds-toggle[aria-checked="true"] i { inset-inline-start: calc(100% - 1.05em - 2px); }
</style>
<span class="wds-toggle-wrap">
  <input type="hidden" name="{{ $name }}" value="0" />
  <button
    type="button"
    class="wds-toggle"
    role="switch"
    aria-checked="{{ $checked ? 'true' : 'false' }}"
    data-wds-toggle
  ><i></i></button>
  <input type="checkbox" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }} hidden data-wds-toggle-input />
  @if ($label)
    <span>{{ $label }}</span>
  @endif
</span>
