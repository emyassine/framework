@props([
  'panel_id' => '',
  'icon' => null,
  'label' => '',
  'href' => '#',
  'active' => false,
  'logo' => '',
  'logo_shape' => 'favicon',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<style>
.wds-rail-item { width: 100%; }
.wds-rail-item > a, .wds-rail-item > button {
  display: flex; align-items: center; justify-content: center;
  height: 60px; width: 100%;
  color: color-mix(in srgb, var(--color-white) 70%, transparent);
  position: relative; background: transparent;
}
.wds-rail-item > a:hover, .wds-rail-item.wds-active > a,
.wds-rail-item > button:hover, .wds-rail-item.wds-active > button {
  color: var(--color-white);
  background: color-mix(in srgb, var(--color-white) 5%, transparent);
}
.wds-rail-item svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 1.75; }
.wds-rail-item img { width: 28px; height: 28px; display: block; }
.wds-rail-item .wds-nav-logo--favicon img { width: 18px; height: 18px; object-fit: contain; }
.wds-rail-item .wds-nav-logo--round img { border-radius: 50%; object-fit: cover; }
.wds-rail-item .wds-nav-logo--square img { border-radius: 6px; object-fit: cover; }
</style>
<li class="wds-rail-item{{ $active ? ' wds-active' : '' }}">
  <a
    href="{{ $href }}"
    title="{{ $label }}"
    data-wds-panel-button
    data-panel-id="{{ $panel_id }}"
    {{ $attributes }}
  >
    @if ($logo !== '')
      <span class="wds-nav-logo--{{ $logo_shape }}"><img src="{{ $logo }}" alt="" /></span>
    @elseif (!empty($icon))
      <span class="wds-icon">{!! icon((string) $icon, 'wds-icon-svg') !!}</span>
    @endif
  </a>
</li>
