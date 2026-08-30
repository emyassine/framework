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
<li class="wds-rail-item{{ $active ? ' wds-active' : '' }}">
  @once('wds.rail.button')
  <style>
.wds-rail-item { width: 100%; display: flex; justify-content: center; }
.wds-rail-item > a,
.wds-rail-item > button {
  display: grid;
  place-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: var(--wds-radius);
  color: var(--wds-text-muted);
  background: var(--gray-100);
  position: relative;
  text-decoration: none;
  transition: background var(--wds-transition), color var(--wds-transition), box-shadow var(--wds-transition);
}
.wds-rail-item > a:hover,
.wds-rail-item > button:hover {
  color: var(--wds-text);
  background: var(--gray-200);
}
.wds-rail-item.wds-active > a,
.wds-rail-item.wds-active > button {
  color: var(--primary-700);
  background: var(--primary-50);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary-500) 50%, transparent);
}
.wds-rail-item svg { width: 1.15rem; height: 1.15rem; stroke: currentColor; fill: none; stroke-width: 1.75; }
.wds-rail-item img { width: 1.5rem; height: 1.5rem; display: block; object-fit: contain; }
.wds-rail-item .wds-nav-logo--round img { border-radius:  var(--wds-radius); object-fit: cover; width: 2.5rem; height: 2.5rem; }
.wds-rail-item .wds-nav-logo--square img { border-radius: var(--wds-radius); object-fit: cover; width: 2.5rem; height: 2.5rem; }
.wds-rail-item .wds-nav-logo--favicon img { width: 1.15rem; height: 1.15rem; object-fit: contain; }
  </style>
  @endonce
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
      <x-webkernel::icon :name="$icon" />
    @endif
  </a>
</li>
