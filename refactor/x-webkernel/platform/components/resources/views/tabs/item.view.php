@props([
  'tab' => '',
  'active' => false,
  'icon' => null,
])
<button
  type="button"
  class="wds-tabs-item{{ $active ? ' wds-active' : '' }}"
  role="tab"
  aria-selected="{{ $active ? 'true' : 'false' }}"
  data-tab="{{ $tab }}"
>
  @if (!empty($icon))
    <span class="wds-icon">{!! icon((string) $icon, 'wds-icon-svg') !!}</span>
  @endif
  <span>{{ $slot }}</span>
</button>
