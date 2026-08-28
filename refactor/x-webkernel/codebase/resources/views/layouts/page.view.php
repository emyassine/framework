<!DOCTYPE html>
<html
  lang="{{ $lang ?? 'en' }}"
  data-wds-theme="{{ $theme ?? 'light' }}"
  data-wds-layout="{{ $wds_layout ?? 'sidebar' }}"
  data-wds-sidebar="{{ $sidebar ?? 'expanded' }}"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  @if (!empty($favicon))
    <link rel="icon" href="{{ $favicon }}" />
  @endif
  <title>{{ $title ?? 'Webkernel' }}</title>
  @include('webkernel::layouts.partials.tokens')
  @include('webkernel::layouts.partials.shell')
  @include('webkernel::layouts.partials.components')
  @stack('styles')
</head>
<body>
<div class="wks-shell">

  {{-- Location: sidebar brand --}}
  <aside class="wks-sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
    <div class="wks-sidebar__brand">
      <div class="wks-brand-icon">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="8" cy="8" r="6"/>
          <path d="M8 2v6l3 3"/>
        </svg>
      </div>
      <span class="wks-brand-name">{{ $brand ?? 'Webkernel' }}</span>
    </div>

    {{-- Location: sidebar (primary nav) --}}
    <div class="wks-sidebar__inner">
      @yield('navigation')
    </div>

    {{-- Location: sidebar footer (account) --}}
    <div class="wks-sidebar-footer">
      @yield('user')
    </div>
  </aside>

  <div class="wks-main">
    <header class="wks-topbar">
      <button class="wks-sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
        <svg viewBox="0 0 16 16"><line x1="1" y1="4" x2="15" y2="4"/><line x1="1" y1="8" x2="15" y2="8"/><line x1="1" y1="12" x2="15" y2="12"/></svg>
      </button>
      <div class="wks-topbar-brand">
        <div class="wks-brand-icon" style="width:24px;height:24px;">
          <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="8" r="6"/><path d="M8 2v6l3 3"/>
          </svg>
        </div>
        <span style="font-size:var(--wds-font-size-base);font-weight:600;letter-spacing:-0.01em;">{{ $brand ?? 'Webkernel' }}</span>
      </div>

      {{-- Location: topnav (used when layout is topnav) --}}
      <nav class="wks-topbar-nav">
        @yield('topnav')
      </nav>

      {{-- Location: breadcrumb --}}
      <div class="wks-breadcrumb">
        @yield('breadcrumb')
      </div>

      <div class="wks-topbar-right">
        <div class="wks-layout-switcher" title="Switch layout">
          <button class="wks-layout-btn{{ ($wds_layout ?? 'sidebar') === 'sidebar' ? ' active' : '' }}" id="btn-layout-sidebar" onclick="setLayout('sidebar')">Sidebar</button>
          <button class="wks-layout-btn{{ ($wds_layout ?? '') === 'topnav' ? ' active' : '' }}" id="btn-layout-topnav" onclick="setLayout('topnav')">Top Nav</button>
          <button class="wks-layout-btn{{ ($wds_layout ?? '') === 'horizontal' ? ' active' : '' }}" id="btn-layout-horizontal" onclick="setLayout('horizontal')">Horizontal</button>
        </div>
        <button class="wks-icon-btn" onclick="toggleTheme()" title="Toggle theme" id="theme-btn">
          <svg id="icon-sun" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3"/><line x1="8" y1="1" x2="8" y2="3"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="1" y1="8" x2="3" y2="8"/><line x1="13" y1="8" x2="15" y2="8"/><line x1="3.2" y1="3.2" x2="4.6" y2="4.6"/><line x1="11.4" y1="11.4" x2="12.8" y2="12.8"/><line x1="12.8" y1="3.2" x2="11.4" y2="4.6"/><line x1="4.6" y1="11.4" x2="3.2" y2="12.8"/></svg>
          <svg id="icon-moon" viewBox="0 0 16 16" style="display:none;"><path d="M12 10A6 6 0 0 1 6 4a6.003 6 0 0 0 6 9 6 6 0 0 1 0-3z"/></svg>
        </button>

        {{-- Location: topbar actions --}}
        @yield('topbar')
      </div>
    </header>

    {{-- Location: horizontal nav (under topbar) --}}
    <div class="wks-horiz-nav">
      @yield('horizontal')
    </div>

    {{-- Location: main content --}}
    <main class="wks-content">
      @yield('content')
    </main>
  </div>
</div>
@include('webkernel::layouts.partials.page-scripts')
@stack('scripts')
</body>
</html>
