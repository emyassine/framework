{{--
  Panel chrome. <x-webkernel::page title="Invoices">body</x-webkernel::page>
--}}
@props([
  'title' => null,
  'header' => null,
  'description' => null,
  'breadcrumbs' => [],
  'header_actions' => [],
  'lang' => 'en',
  'theme' => null,
  'csrf' => true,
  'sidebar' => 'expanded',
  'brand' => null,
  'favicon' => null,
  'logo' => null,
])
@php
  $title = (string) ($title ?? '');
  $header = (string) ($header ?? $title);
  $description = $description !== null && $description !== '' ? (string) $description : '';
  $breadcrumbs = \is_array($breadcrumbs) ? $breadcrumbs : [];
  $header_actions = \is_array($header_actions) ? $header_actions : [];
  $brand = $brand ?? \Webkernel\Config\Config::get('app.name');
  if ($logo === null && \function_exists('webkernel_branding_url')) {
    $logo = webkernel_branding_url('webkernel-favicon');
  }
  $panel_api = \webapp()->panel();
  $apps = $panel_api->all();
  $current = $panel_api->matching_path();
  $current_id = \is_array($current) ? (string) ($current['id'] ?? '') : '';
  $groups = \is_array($current) ? ($current['navigation'] ?? []) : [];
  $current_label = \is_array($current) ? (string) ($current['label'] ?? '') : (string) ($brand ?? 'Webkernel');
  $document_title = $title !== '' ? $title : $header;
  $document_description = $description !== '' ? $description : $header;
@endphp
<style>
	.wds-layout { display: flex; min-height: 100vh; background: var(--wds-bg); color: var(--wds-text); }
	.wds-topbar {
	  height: var(--wds-topbar-height);
	  display: flex; align-items: center; gap: 0.75rem;
	  padding: 0 1rem; border-bottom: 1px solid var(--wds-border);
	  background: var(--wds-surface); color: var(--wds-text);
	}
	.wds-breadcrumbs { display: flex; align-items: center; gap: 0.4rem; font-size: var(--wds-text-sm); color: var(--wds-text-muted); min-width: 0; }
	.wds-breadcrumbs a { color: var(--wds-text-muted); }
	.wds-breadcrumbs a:hover { color: var(--wds-text); }
	.wds-topbar-end { margin-inline-start: auto; display: flex; align-items: center; gap: 0.5rem; }
	.wds-sidebar-open-btn, .wds-icon-btn {
	  width: 32px; height: 32px; display: grid; place-content: center;
	  color: var(--wds-text); border-radius: var(--wds-radius);
	}
	.wds-sidebar-open-btn:hover, .wds-icon-btn:hover { background: var(--wds-bg-subtle); }
	.wds-sidebar-open-btn svg, .wds-icon-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.5; }
	.wds-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
	.wds-header-heading { font-size: 26px; font-weight: 500; letter-spacing: -0.02em; line-height: 1.2; color: var(--wds-text); }
	.wds-header-subheading { margin-top: 0.25rem; font-size: var(--wds-text-sm); color: var(--wds-text-muted); }
	.wds-header-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
	.wds-page { display: flex; flex-direction: column; gap: 1.25rem; color: var(--wds-text); }
	.wds-page-content { min-width: 0; color: var(--wds-text); }
	.wds-user-menu { position: relative; }
	.wds-user-menu-trigger { display: flex; align-items: center; gap: 0.5rem; color: var(--wds-text); padding: 0.25rem 0.4rem; border-radius: var(--wds-radius); }
	.wds-user-menu-trigger:hover { background: var(--wds-bg-subtle); }
	.wds-user-menu-panel {
	  display: none; position: absolute; inset-inline-end: 0; top: calc(100% + 0.4rem);
	  min-width: 12rem; background: var(--wds-surface); border: 1px solid var(--wds-border);
	  border-radius: 8px; z-index: 70; padding: 4px;
	}
	.wds-user-menu.wds-open .wds-user-menu-panel { display: block; }
	.wds-user-menu-panel a { display: flex; align-items: center; gap: 0.6em; padding: 0.55em 0.75em; border-radius: 6px; color: var(--wds-text); }
	.wds-user-menu-panel a:hover { background: var(--wds-bg-subtle); }
</style>
<x-webkernel::page.base :title="$document_title" :lang="$lang" :theme="$theme" :csrf="$csrf" :favicon="$favicon" :description="$document_description" layout="sidebar">
  <script>
  (function(d){var s=localStorage.getItem('wds-sidebar');if(s)d.dataset.wdsSidebar=s;})(document.documentElement);</script>
  <div class="wds-layout" data-wds-sidebar-root>
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
              $app_active = $app_id !== '' && $app_id === $current_id;
            @endphp
            <x-webkernel::page.panel.button
              :panel_id="$app_id"
              :icon="$app_icon"
              :label="$app_label"
              :href="$app_href"
              :active="$app_active"
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
                        @php
                          $item_label = (string) ($item['label'] ?? '');
                          if (\str_starts_with($item_label, 'panel.')) {
                            $item_label = lang($item_label);
                          }
                          $item_href = (string) ($item['href'] ?? '#');
                          $item_icon = (string) ($item['icon'] ?? 'package');
                          $item_active = $panel_api->href_is_active($item_href);
                        @endphp
                        <li>
                          <x-webkernel::page.menu.element
                            :label="$item_label"
                            :href="$item_href"
                            :icon="$item_icon"
                            :active="$item_active"
                          />
                        </li>
                      @endforeach
                    </ul>
                  </details>
                @else
                  <ul>
                    @foreach (($group['items'] ?? []) as $item)
                      @php
                        $item_label = (string) ($item['label'] ?? '');
                        if (\str_starts_with($item_label, 'panel.')) {
                          $item_label = lang($item_label);
                        }
                        $item_href = (string) ($item['href'] ?? '#');
                        $item_icon = (string) ($item['icon'] ?? 'package');
                        $item_active = $panel_api->href_is_active($item_href);
                      @endphp
                      <li>
                        <x-webkernel::page.menu.element
                          :label="$item_label"
                          :href="$item_href"
                          :icon="$item_icon"
                          :active="$item_active"
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
          @foreach ($breadcrumbs as $i => $crumb)
            @php
              $crumb_label = \is_array($crumb) ? (string) ($crumb['label'] ?? '') : (string) $crumb;
              $crumb_href = \is_array($crumb) ? (string) ($crumb['href'] ?? '') : '';
            @endphp
            @if ($i > 0)
              <span aria-hidden="true">/</span>
            @endif
            @if ($crumb_href !== '')
              <a href="{{ $crumb_href }}">{{ $crumb_label }}</a>
            @else
              <span>{{ $crumb_label }}</span>
            @endif
          @endforeach
        </div>
        <div class="wds-topbar-end">
          <x-webkernel::language-selector />
          <button class="wds-icon-btn" onclick="toggleTheme()" title="{{ lang('panel.theme') }}" id="theme-btn">
            <svg id="icon-sun" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3"/><line x1="8" y1="1" x2="8" y2="3"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="1" y1="8" x2="3" y2="8"/><line x1="13" y1="8" x2="15" y2="8"/><line x1="3.2" y1="3.2" x2="4.6" y2="4.6"/><line x1="11.4" y1="11.4" x2="12.8" y2="12.8"/><line x1="12.8" y1="3.2" x2="11.4" y2="4.6"/><line x1="4.6" y1="11.4" x2="3.2" y2="12.8"/></svg>
            <svg id="icon-moon" viewBox="0 0 16 16" style="display:none;"><path d="M12 10A6 6 0 0 1 6 4a6.003 6 0 0 0 6 9 6 6 0 0 1 0-3z"/></svg>
          </button>
          @include('webkernel::system.user', ['brand' => $brand])
          {!! $topbar ?? '' !!}
        </div>
      </header>
      <main class="wds-main">
        <div class="wds-page">
          @if ($header !== '' || $description !== '' || $header_actions !== [])
            <header class="wds-header">
              <div>
                @if ($header !== '')
                  <h1 class="wds-header-heading">{{ $header }}</h1>
                @endif
                @if ($description !== '')
                  <p class="wds-header-subheading">{{ $description }}</p>
                @endif
              </div>
              @if ($header_actions !== [])
                <div class="wds-header-actions">
                  @foreach ($header_actions as $action)
                    {!! $action !!}
                  @endforeach
                </div>
              @endif
            </header>
          @endif
          <div class="wds-page-content">
            {!! $slot !!}
          </div>
        </div>
      </main>
    </x-webkernel::page.main>

    <x-webkernel::page.aside position="right" collapsed>
      {!! $aside ?? '' !!}
    </x-webkernel::page.aside>
  </div>
</x-webkernel::page.base>
