{{--
  Panel page. <x-webkernel::page title="Invoices">body</x-webkernel::page>
  Regions: rail, sidebar, main, aside. Not nested page.* tags.
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
  $layout_flags = \is_array($current) && isset($current['layout']) && \is_array($current['layout'])
      ? $current['layout']
      : [];
  $has_panel_sidebar = (bool) ($layout_flags['panel_sidebar'] ?? true);
  $has_sidebar = (bool) ($layout_flags['sidebar'] ?? true);
  $has_topbar = (bool) ($layout_flags['topbar'] ?? true);
  $has_left = $has_panel_sidebar || $has_sidebar;
@endphp
<x-webkernel::page.base :title="$document_title" :lang="$lang" :theme="$theme" :csrf="$csrf" :favicon="$favicon" :description="$document_description" layout="sidebar">
  <script>
  (function(d){var s=localStorage.getItem('w-sidebar');if(s)d.dataset.wSidebar=s;})(document.documentElement);</script>
  <div class="w-layout" data-w-sidebar-root>
    @if ($has_left)
      <x-webkernel::sidebar.group position="left">
        @if ($has_panel_sidebar)
          <x-webkernel::rail>
            <div class="w-rail-brand">
              <a class="w-rail-logo" href="/" title="{{ $brand }}">
                <span class="w-rail-logo-mark">
                  @if (!empty($logo))
                    <img src="{{ $logo }}" alt="" width="24" height="24" />
                  @else
                    <span class="w-logo-icon">
                      <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="8" cy="8" r="6"/><path d="M8 2v6l3 3"/>
                      </svg>
                    </span>
                  @endif
                </span>
              </a>
            </div>
            @auth
              <div class="w-rail-account">
                <a class="w-rail-avatar-btn" href="/system" title="{{ lang('panel.profile') }}">
                  <x-webkernel::icon name="contact" />
                </a>
              </div>
            @endauth
            <ul class="w-rail-list">
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
                <x-webkernel::rail.button
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
          </x-webkernel::rail>
        @endif

        @if ($has_sidebar)
          <x-webkernel::sidebar>
            <x-webkernel::sidebar.header :title="$current_label" />
            <div class="w-drawer-body">
              <ul class="w-nav-list">
                <li>
                  <button type="button" class="w-nav-search" onclick="document.dispatchEvent(new CustomEvent('w-search'))">
                    <span class="w-nav-icon" aria-hidden="true">{!! icon('search', 'w-icon-svg') !!}</span>
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
                  <li class="{{ $is_group ? 'w-nav-group' : '' }}">
                    @if ($is_group)
                      <details open>
                        <summary class="w-nav-summary">
                          <span class="w-nav-icon" aria-hidden="true">{!! icon($group_icon, 'w-icon-svg') !!}</span>
                          <span>{{ $group_label }}</span>
                          <span class="w-nav-caret">{!! icon('chevron-down', 'w-icon-svg') !!}</span>
                        </summary>
                        <ul class="w-nav-sub">
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
                              <x-webkernel::sidebar.item
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
                            <x-webkernel::sidebar.item
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
          </x-webkernel::sidebar>
        @endif
      </x-webkernel::sidebar.group>
    @endif

    <x-webkernel::main>
      @if ($has_topbar)
        <x-webkernel::topbar :breadcrumbs="$breadcrumbs" :brand="$brand">
          @if (isset($topbar) && $topbar !== '')
            {!! $topbar !!}
          @endif
        </x-webkernel::topbar>
      @endif
      <main class="w-main">
        <div class="w-page">
          @if ($header !== '' || $description !== '' || $header_actions !== [])
            <header class="w-header">
              <div>
                @if ($header !== '')
                  <h1 class="w-header-heading">{{ $header }}</h1>
                @endif
                @if ($description !== '')
                  <p class="w-header-subheading">{{ $description }}</p>
                @endif
              </div>
              @if ($header_actions !== [])
                <div class="w-header-actions">
                  @foreach ($header_actions as $action)
                    {!! $action !!}
                  @endforeach
                </div>
              @endif
            </header>
          @endif
          <div class="w-page-content">
            {!! $slot !!}
          </div>
        </div>
      </main>
    </x-webkernel::main>

    <x-webkernel::aside position="right" collapsed>
      {!! $aside ?? '' !!}
    </x-webkernel::aside>
  </div>
</x-webkernel::page.base>
