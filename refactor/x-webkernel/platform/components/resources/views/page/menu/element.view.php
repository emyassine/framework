@props([
  'label' => '',
  'href' => '#',
  'icon' => null,
  'active' => false,
])
<a href="{{ $href }}" class="wds-menu-element wds-nav-link{{ $active ? ' wds-active' : '' }}"{!! $active ? ' aria-current="page"' : '' !!}>
  @if (!empty($icon))
    <span class="wds-nav-icon" aria-hidden="true">{!! icon((string) $icon, 'wds-icon-svg') !!}</span>
  @endif
  <span>{{ $label }}</span>
</a>
