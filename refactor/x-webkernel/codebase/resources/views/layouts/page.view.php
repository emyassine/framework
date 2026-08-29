@props([
  'lang' => 'en',
  'theme' => null,
  'layout' => 'sidebar',
  'sidebar' => 'expanded',
  'brand' => null,
  'favicon' => null,
  'logo' => null,
  'description' => null,
  'csrf' => true,
])
@php
  $brand = $brand ?? \Webkernel\Config\Config::get('app.name');
  $theme = $theme ?? (\Webkernel\Config\Config::get('ui.dark_mode', true) ? 'dark' : 'light');
  $description = $description ?? ($brand.' control panel');
  if ($favicon === null && \function_exists('webkernel_branding_url')) {
    $favicon = webkernel_branding_url('webkernel-favicon');
  }
  if ($logo === null && \function_exists('webkernel_branding_url')) {
    $logo = webkernel_branding_url('webkernel-favicon');
  }
  $panel_api = webapp()->panel();
  $apps = $panel_api->all();
  $current_app = $panel_api->matching_path();
  $current_id = \is_array($current_app) ? (string) ($current_app['id'] ?? '') : '';
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
  <script>(function(d){var t=localStorage.getItem('wds-theme');var l=localStorage.getItem('wds-layout');var s=localStorage.getItem('wds-sidebar');if(t)d.dataset.wdsTheme=t;if(l)d.dataset.wdsLayout=l;if(s&&(!l||l==='sidebar'))d.dataset.wdsSidebar=s;})(document.documentElement);</script>
  @if ($csrf)
    {!! \Webkernel\Csrf::meta() !!}
  @endif
  @if (!empty($favicon))
    <link rel="icon" href="{{ $favicon }}" />
  @endif
  <title>@yield('title')</title>
  <meta name="description" content="{{ $description }}" />
  @include('webkernel::layouts.partials.typography')
  <link rel="stylesheet" href="{{ \Webkernel\Platform\Wds::css_href() }}">
  @stack('styles')
  @stack('head')
</head>
<body>
<div class="wds-layout">

  @include('webkernel::panels.chrome.rail', ['brand' => $brand, 'logo' => $logo])

  <div class="wds-main-ctn">
    <header class="wds-topbar">
      <button class="wds-sidebar-open-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
        <svg viewBox="0 0 16 16"><line x1="1" y1="4" x2="15" y2="4"/><line x1="1" y1="8" x2="15" y2="8"/><line x1="1" y1="12" x2="15" y2="12"/></svg>
      </button>
      <div class="wds-topbar-logo">
        @if (!empty($logo))
          <img class="wds-logo-img" src="{{ $logo }}" alt="{{ $brand ?? 'Webkernel' }}" width="24" height="24" />
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
        @foreach ($apps as $app)
          @php
            $app_id = (string) ($app['id'] ?? '');
            $app_href = (string) ($app['href'] ?? '/'.$app_id);
            $app_label = (string) ($app['label'] ?? \ucfirst($app_id));
          @endphp
          <a href="{{ $app_href }}" class="wds-topbar-item{{ $app_id === $current_id ? ' wds-active' : '' }}">{{ $app_label }}</a>
        @endforeach
      </nav>

      <label class="wds-search">
        <span class="wds-icon">{!! icon('search', 'wds-icon-svg') !!}</span>
        <input type="search" placeholder="Search..." autocomplete="off" />
      </label>

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
        @include('webkernel::panels.system.user', ['brand' => $brand])
        @yield('topbar')
      </div>
    </header>

    <div class="wds-horizontal-nav">
      @foreach ((\is_array($current_app) ? ($current_app['navigation'] ?? []) : []) as $group)
        @foreach (($group['items'] ?? []) as $item)
          @php
            $item_href = (string) ($item['href'] ?? '#');
          @endphp
          <a href="{{ $item_href }}" class="wds-horizontal-nav-item{{ $panel_api->href_is_active($item_href) ? ' wds-active' : '' }}">{{ $item['label'] ?? '' }}</a>
        @endforeach
      @endforeach
    </div>

    <main class="wds-main">
      <div class="wds-breadcrumbs">
        @yield('breadcrumb')
      </div>
      @yield('content')
    </main>
  </div>
</div>
@include('webkernel::wds.script')
@stack('scripts')
</body>
</html>
