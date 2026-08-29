@props([
  'lang' => 'en',
  'theme' => null,
  'layout' => 'sidebar',
  'sidebar' => 'expanded',
  'brand' => null,
  'favicon' => null,
  'logo' => null,
  'csrf' => true,
])
@php
  $brand = $brand ?? \Webkernel\Config\Config::get('app.name');
  $theme = $theme ?? (\Webkernel\Config\Config::get('ui.dark_mode', true) ? 'dark' : 'light');
  if ($favicon === null && \function_exists('webkernel_branding_url')) {
    $favicon = webkernel_branding_url('webkernel-favicon');
  }
  if ($logo === null && \function_exists('webkernel_branding_url')) {
    $logo = webkernel_branding_url($theme === 'dark' ? 'webkernel-logo-dark' : 'webkernel-logo-light');
  }
@endphp
<!DOCTYPE html>
<html
  lang="{{ $lang }}"
  data-wds-theme="{{ $theme }}"
  data-wds-layout="{{ $layout }}"
  data-wds-sidebar="{{ $sidebar }}"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  @if ($csrf)
    {!! \Webkernel\Csrf::meta() !!}
  @endif
  @if (!empty($favicon))
    <link rel="icon" href="{{ $favicon }}" />
  @endif
  <title>@yield('title')</title>
  @include('webkernel::layouts.partials.typography')
  <link rel="stylesheet" href="{{ \Webkernel\Platform\Wds::css_href() }}">
  @stack('styles')
  @stack('head')
</head>
<body>
<div class="wds-layout">

  <aside class="wds-sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
    <div class="wds-sidebar-header">
      @if (!empty($logo))
        <img class="wds-logo-img" src="{{ $logo }}" alt="{{ $brand ?? 'Webkernel' }}" />
      @else
        <div class="wds-logo-icon">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="8" r="6"/>
            <path d="M8 2v6l3 3"/>
          </svg>
        </div>
      @endif
      <span class="wds-logo">{{ $brand ?? 'Webkernel' }}</span>
    </div>

    <div class="wds-sidebar-nav">
      @yield('navigation')
    </div>

    <div class="wds-sidebar-footer">
      @yield('user')
    </div>
  </aside>

  <div class="wds-main-ctn">
    <header class="wds-topbar">
      <button class="wds-sidebar-open-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
        <svg viewBox="0 0 16 16"><line x1="1" y1="4" x2="15" y2="4"/><line x1="1" y1="8" x2="15" y2="8"/><line x1="1" y1="12" x2="15" y2="12"/></svg>
      </button>
      <div class="wds-topbar-logo">
        @if (!empty($logo))
          <img class="wds-logo-img" src="{{ $logo }}" alt="{{ $brand ?? 'Webkernel' }}" />
        @else
          <div class="wds-logo-icon" style="width:24px;height:24px;">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="8" cy="8" r="6"/><path d="M8 2v6l3 3"/>
            </svg>
          </div>
        @endif
        <span style="font-size:var(--wds-text-base);font-weight:600;letter-spacing:-0.01em;">{{ $brand ?? 'Webkernel' }}</span>
      </div>

      <nav class="wds-topbar-nav">
        @yield('navigation')
      </nav>

      <div class="wds-breadcrumbs">
        @yield('breadcrumb')
      </div>

      <div class="wds-topbar-end">
        <div class="wds-layout-switcher" title="Switch layout">
          <button class="wds-layout-switcher-btn{{ ($layout ?? 'sidebar') === 'sidebar' ? ' wds-active' : '' }}" id="btn-layout-sidebar" onclick="setLayout('sidebar')">Sidebar</button>
          <button class="wds-layout-switcher-btn{{ ($layout ?? '') === 'topnav' ? ' wds-active' : '' }}" id="btn-layout-topnav" onclick="setLayout('topnav')">Top Nav</button>
          <button class="wds-layout-switcher-btn{{ ($layout ?? '') === 'horizontal' ? ' wds-active' : '' }}" id="btn-layout-horizontal" onclick="setLayout('horizontal')">Horizontal</button>
        </div>
        <button class="wds-icon-btn" onclick="toggleTheme()" title="Toggle theme" id="theme-btn">
          <svg id="icon-sun" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3"/><line x1="8" y1="1" x2="8" y2="3"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="1" y1="8" x2="3" y2="8"/><line x1="13" y1="8" x2="15" y2="8"/><line x1="3.2" y1="3.2" x2="4.6" y2="4.6"/><line x1="11.4" y1="11.4" x2="12.8" y2="12.8"/><line x1="12.8" y1="3.2" x2="11.4" y2="4.6"/><line x1="4.6" y1="11.4" x2="3.2" y2="12.8"/></svg>
          <svg id="icon-moon" viewBox="0 0 16 16" style="display:none;"><path d="M12 10A6 6 0 0 1 6 4a6.003 6 0 0 0 6 9 6 6 0 0 1 0-3z"/></svg>
        </button>

        @yield('topbar')
      </div>
    </header>

    <div class="wds-horizontal-nav">
      @yield('navigation')
    </div>

    <main class="wds-main">
      @yield('content')
    </main>
  </div>
</div>
@include('webkernel::wds.script')
@stack('scripts')
</body>
</html>
