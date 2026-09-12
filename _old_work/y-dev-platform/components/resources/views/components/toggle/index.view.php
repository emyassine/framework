@props([
  'name' => '',
  'checked' => false,
  'label' => null,
])
<span class="w-toggle-wrap">
  <input type="hidden" name="{{ $name }}" value="0" />
  <button
    type="button"
    class="w-toggle"
    role="switch"
    aria-checked="{{ $checked ? 'true' : 'false' }}"
    data-w-toggle
  ><i></i></button>
  <input type="checkbox" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }} hidden data-w-toggle-input />
  @if ($label)
    <span>{{ $label }}</span>
  @endif
</span>
