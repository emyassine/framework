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
<li class="w-rail-item{{ $active ? ' w-active' : '' }}">
  <a
    href="{{ $href }}"
    x-tooltip="{{ $label }}"
    x-tooltip-placement="right"
    data-w-panel-button
    data-panel-id="{{ $panel_id }}"
    {{ $attributes }}
  >
    @if ($logo !== '')
      <span class="w-nav-logo--{{ $logo_shape }}"><img src="{{ $logo }}" alt="" /></span>
    @elseif (!empty($icon))
      <x-webkernel::icon :name="$icon" />
    @endif
  </a>
</li>
