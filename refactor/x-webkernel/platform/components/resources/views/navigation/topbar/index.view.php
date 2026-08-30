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
@endphp
@once('w.topbar.js')
<script>
(function () {
  window.toggleTheme = function () {
    var html = document.documentElement;
    var next = html.dataset.wTheme === 'dark' ? 'light' : 'dark';
    html.dataset.wTheme = next;
    var sun = document.getElementById('icon-sun');
    var moon = document.getElementById('icon-moon');
    if (sun) sun.style.display = next === 'dark' ? 'none' : 'block';
    if (moon) moon.style.display = next === 'dark' ? 'block' : 'none';
    localStorage.setItem('w-theme', next);
  };
  window.toggleSidebar = function () {
    var html = document.documentElement;
    html.dataset.wSidebar = html.dataset.wSidebar === 'collapsed' ? 'expanded' : 'collapsed';
    localStorage.setItem('w-sidebar', html.dataset.wSidebar);
  };
  if (document.documentElement.dataset.wTheme === 'dark') {
    var sun = document.getElementById('icon-sun');
    var moon = document.getElementById('icon-moon');
    if (sun) sun.style.display = 'none';
    if (moon) moon.style.display = 'block';
  }
})();
</script>
@endonce
<header {{ $attributes->class('w-topbar') }}>
  <x-webkernel::icon-button
    icon="menu"
    :label="lang('panel.toggle_sidebar')"
    size="sm"
    color="gray"
    onclick="toggleSidebar()"
  />
  <x-webkernel::breadcrumbs :breadcrumbs="$breadcrumbs" />
  <div class="w-topbar-end">
    <x-webkernel::language-selector />
    <button type="button" class="w-icon-btn" onclick="toggleTheme()" title="{{ lang('panel.theme') }}" id="theme-btn">
      <svg id="icon-sun" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="3"/><line x1="8" y1="1" x2="8" y2="3"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="1" y1="8" x2="3" y2="8"/><line x1="13" y1="8" x2="15" y2="8"/><line x1="3.2" y1="3.2" x2="4.6" y2="4.6"/><line x1="11.4" y1="11.4" x2="12.8" y2="12.8"/><line x1="12.8" y1="3.2" x2="11.4" y2="4.6"/><line x1="4.6" y1="11.4" x2="3.2" y2="12.8"/></svg>
      <svg id="icon-moon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;"><path d="M12 10A6 6 0 0 1 6 4a6.003 6 0 0 0 6 9 6 6 0 0 1 0-3z"/></svg>
    </button>
    @include('webkernel-system::system.user', ['brand' => $brand])
    {!! $slot !!}
  </div>
</header>
