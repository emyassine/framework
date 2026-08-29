@props([
  'name' => '',
  'checked' => false,
  'label' => null,
])
<span class="wds-toggle-wrap" style="display:inline-flex;align-items:center;gap:0.6em;">
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
