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
  if (isset($_GET['lang']) && \is_string($_GET['lang']) && \class_exists(\Webkernel\I18n\I18nContext::class, true)) {
    \Webkernel\I18n\I18nContext::set_locale($_GET['lang']);
  }
  $brand = $brand ?? \Webkernel\Config\Config::get('app.name');
  $theme = $theme ?? (\Webkernel\Config\Config::get('ui.dark_mode', true) ? 'dark' : 'light');
  $description = $description ?? ($brand.' control panel');
  if ($favicon === null && \function_exists('webkernel_branding_url')) {
    $favicon = webkernel_branding_url('webkernel-favicon');
  }
  if ($logo === null && \function_exists('webkernel_branding_url')) {
    $logo = webkernel_branding_url('webkernel-favicon');
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
  <script>(function(d){var t=localStorage.getItem('wds-theme');var s=localStorage.getItem('wds-sidebar');if(t)d.dataset.wdsTheme=t;if(s)d.dataset.wdsSidebar=s;})(document.documentElement);</script>
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
  @include('webkernel::panels.chrome.nav', ['brand' => $brand, 'logo' => $logo])

  <div class="wds-main-ctn">
    <header class="wds-topbar">
      <button class="wds-sidebar-open-btn" onclick="toggleSidebar()" aria-label="{{ lang('panel.toggle_sidebar') }}" title="{{ lang('panel.toggle_sidebar') }}">
        <svg viewBox="0 0 16 16"><line x1="1" y1="4" x2="15" y2="4"/><line x1="1" y1="8" x2="15" y2="8"/><line x1="1" y1="12" x2="15" y2="12"/></svg>
      </button>
      <div class="wds-breadcrumbs">
        @yield('breadcrumb')
      </div>
      <div class="wds-topbar-end">
        <x-webkernel::language-selector />
        <button class="wds-icon-btn" onclick="toggleTheme()" title="{{ lang('panel.theme') }}" id="theme-btn">
          <svg id="icon-sun" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3"/><line x1="8" y1="1" x2="8" y2="3"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="1" y1="8" x2="3" y2="8"/><line x1="13" y1="8" x2="15" y2="8"/><line x1="3.2" y1="3.2" x2="4.6" y2="4.6"/><line x1="11.4" y1="11.4" x2="12.8" y2="12.8"/><line x1="12.8" y1="3.2" x2="11.4" y2="4.6"/><line x1="4.6" y1="11.4" x2="3.2" y2="12.8"/></svg>
          <svg id="icon-moon" viewBox="0 0 16 16" style="display:none;"><path d="M12 10A6 6 0 0 1 6 4a6.003 6 0 0 0 6 9 6 6 0 0 1 0-3z"/></svg>
        </button>
        @include('webkernel::panels.system.user', ['brand' => $brand])
        @yield('topbar')
      </div>
    </header>

    <main class="wds-main">
      @yield('content')
    </main>
  </div>
</div>
@include('webkernel::wds.script')
@stack('scripts')
</body>
</html>
