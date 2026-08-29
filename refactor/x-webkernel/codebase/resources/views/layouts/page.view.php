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
  $lang = \function_exists('i18n_current_lang') ? i18n_current_lang() : ($lang ?? 'en');
  $brand = $brand ?? \Webkernel\Config\Config::get('app.name');
  $theme = $theme ?? (\Webkernel\Config\Config::get('ui.dark_mode', true) ? 'dark' : 'light');
  $description = $description ?? ($brand.' control panel');
  if ($favicon === null && \function_exists('webkernel_branding_url')) {
    $favicon = webkernel_branding_url('webkernel-favicon');
  }
  if ($logo === null && \function_exists('webkernel_branding_url')) {
    $logo = webkernel_branding_url('webkernel-favicon');
  }
  $panel_api = \webapp()->panel();
  $apps = $panel_api->all();
  $current = $panel_api->matching_path();
  $current_id = \is_array($current) ? (string) ($current['id'] ?? '') : '';
  $groups = \is_array($current) ? ($current['navigation'] ?? []) : [];
  $current_label = \is_array($current) ? (string) ($current['label'] ?? '') : (string) ($brand ?? 'Webkernel');
  $current_home = \is_array($current) ? (string) ($current['home_url'] ?? $current['href'] ?? '/') : '/';
@endphp
<!DOCTYPE html>
<html
  lang="{{ $lang }}"
  dir="{{ \function_exists('i18n_direction') ? i18n_direction($lang) : 'ltr' }}"
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
  <x-webkernel::page.sidebar.group position="left">
    <x-webkernel::page.panels.rail>
      <a class="wds-rail-logo" href="/" title="{{ $brand }}">
        @if (!empty($logo))
          <img src="{{ $logo }}" alt="" width="30" height="30" />
        @else
          <span class="wds-logo-icon">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <circle cx="8" cy="8" r="6"/><path d="M8 2v6l3 3"/>
            </svg>
          </span>
        @endif
      </a>
      <ul class="wds-rail-list">
        @foreach ($apps as $app)
          @php
            $app_id = (string) ($app['id'] ?? '');
            $app_href = (string) ($app['home_url'] ?? $app['href'] ?? '/'.$app_id);
            $app_label = (string) ($app['label'] ?? \ucfirst($app_id));
            $app_icon = (string) ($app['icon'] ?? 'package');
            $app_logo = (string) \Webkernel\Config\Config::get('panels.'.$app_id.'.logo', '');
            $app_shape = (string) \Webkernel\Config\Config::get('panels.'.$app_id.'.logo_shape', 'favicon');
            if ($app_shape !== 'round' && $app_shape !== 'square' && $app_shape !== 'favicon') {
              $app_shape = 'favicon';
            }
          @endphp
          <x-webkernel::page.panel.button
            :panel_id="$app_id"
            :icon="$app_icon"
            :label="$app_label"
            :href="$app_href"
            :active="$app_id !== '' && $app_id === $current_id"
            :logo="$app_logo"
            :logo_shape="$app_shape"
          />
        @endforeach
      </ul>
    </x-webkernel::page.panels.rail>

    <x-webkernel::page.menu.drawer>
      <x-webkernel::page.menu.header :title="$current_label" />
      <div class="wds-drawer-body">
        <ul class="wds-nav-list">
          <li>
            <button type="button" class="wds-nav-search" onclick="document.dispatchEvent(new CustomEvent('wds-search'))">
              <span class="wds-nav-icon" aria-hidden="true">{!! icon('search', 'wds-icon-svg') !!}</span>
              {{ lang('panel.search') }}
            </button>
          </li>
          @foreach ($groups as $group)
            @php
              $group_label = (string) ($group['label'] ?? '');
              if (\str_starts_with($group_label, 'panel.')) {
                $group_label = lang($group_label);
              }
              $group_icon = (string) ($group['icon'] ?? 'folder');
              $is_group = $group_label !== '';
            @endphp
            <li class="{{ $is_group ? 'wds-nav-group' : '' }}">
              @if ($is_group)
                <details open>
                  <summary class="wds-nav-summary">
                    <span class="wds-nav-icon" aria-hidden="true">{!! icon($group_icon, 'wds-icon-svg') !!}</span>
                    <span>{{ $group_label }}</span>
                    <span class="wds-nav-caret">{!! icon('chevron-down', 'wds-icon-svg') !!}</span>
                  </summary>
                  <ul class="wds-nav-sub">
                    @foreach (($group['items'] ?? []) as $item)
                      <li>
                        <x-webkernel::page.menu.element
                          :label="\str_starts_with((string) ($item['label'] ?? ''), 'panel.') ? lang((string) $item['label']) : (string) ($item['label'] ?? '')"
                          :href="(string) ($item['href'] ?? '#')"
                          :icon="(string) ($item['icon'] ?? 'package')"
                          :active="$panel_api->href_is_active((string) ($item['href'] ?? '#'))"
                        />
                      </li>
                    @endforeach
                  </ul>
                </details>
              @else
                <ul>
                  @foreach (($group['items'] ?? []) as $item)
                    <li>
                      <x-webkernel::page.menu.element
                        :label="\str_starts_with((string) ($item['label'] ?? ''), 'panel.') ? lang((string) $item['label']) : (string) ($item['label'] ?? '')"
                        :href="(string) ($item['href'] ?? '#')"
                        :icon="(string) ($item['icon'] ?? 'package')"
                        :active="$panel_api->href_is_active((string) ($item['href'] ?? '#'))"
                      />
                    </li>
                  @endforeach
                </ul>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    </x-webkernel::page.menu.drawer>
  </x-webkernel::page.sidebar.group>

  <x-webkernel::page.main>
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
        @include('webkernel::system.user', ['brand' => $brand])
        @yield('topbar')
      </div>
    </header>
    <main class="wds-main">
      @yield('content')
    </main>
  </x-webkernel::page.main>

  <x-webkernel::page.aside position="right" collapsed>
    {!! $aside ?? '' !!}
  </x-webkernel::page.aside>
</div>
@include('webkernel::wds.script')
@stack('scripts')
</body>
</html>
