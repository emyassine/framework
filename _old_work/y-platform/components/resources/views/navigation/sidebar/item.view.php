@props([
  'label' => '',
  'href' => '#',
  'icon' => null,
  'active' => false,
])
<a href="{{ $href }}" class="w-menu-element w-nav-link{{ $active ? ' w-active' : '' }}"{!! $active ? ' aria-current="page"' : '' !!}>
  @if (!empty($icon))
    <span class="w-nav-icon" aria-hidden="true"><x-webkernel::icon :name="$icon" /></span>
  @endif
  <span>{{ $label }}</span>
</a>
