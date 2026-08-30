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
