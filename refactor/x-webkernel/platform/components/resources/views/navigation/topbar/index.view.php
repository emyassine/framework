{{--
  <x-webkernel::topbar :breadcrumbs="$breadcrumbs" />
--}}
@props([
  'breadcrumbs' => [],
  'brand' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $breadcrumbs = \is_array($breadcrumbs) ? $breadcrumbs : [];
  $slot = $slot ?? '';
@endphp
<header {{ $attributes->class('w-topbar') }}>
  <x-webkernel::button-icon
    icon="menu"
    :label="lang('panel.toggle_sidebar')"
    size="sm"
    color="gray"
    onclick="toggleSidebar()"
  />
  <x-webkernel::breadcrumbs :breadcrumbs="$breadcrumbs" />
  <div class="w-topbar-end">
    <x-webkernel::language-selector />
    <x-webkernel::button-icon
      icon="sun"
      :label="lang('panel.theme')"
      size="sm"
      color="gray"
      id="theme-btn"
      onclick="toggleTheme()"
    />
    @include('webkernel-system::system.user', ['brand' => $brand])
    {!! $slot !!}
  </div>
</header>

