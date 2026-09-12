@props([
  'href' => '#',
  'icon' => null,
  'active' => false,
])
<a href="{{ $href }}" class="w-sidebar-item{{ !empty($active) ? ' w-active' : '' }}">
  @if (!empty($icon))
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="w-sidebar-item-label">{!! $slot !!}</span>
</a>
