@props([
  'tab' => '',
  'active' => false,
  'icon' => null,
])
<button
  type="button"
  class="w-tabs-item{{ $active ? ' w-active' : '' }}"
  role="tab"
  aria-selected="{{ $active ? 'true' : 'false' }}"
  data-tab="{{ $tab }}"
>
  <span style="display: inline-flex; align-items: center; gap: 8px;">
    @if (!empty($icon))
      <x-webkernel::icon :name="$icon" />
    @endif
    <span>{{ $slot }}</span>
  </span>
</button>
