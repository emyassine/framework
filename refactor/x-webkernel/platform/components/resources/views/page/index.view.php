{{--
  Panel chrome. <x-webkernel::page title="Invoices">body</x-webkernel::page>
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
  $chrome = \is_array($current) && isset($current['chrome']) && \is_array($current['chrome'])
      ? $current['chrome']
      : [];
  $has_panel_sidebar = (bool) ($chrome['panel_sidebar'] ?? true);
  $has_sidebar = (bool) ($chrome['sidebar'] ?? true);
  $has_topbar = (bool) ($chrome['topbar'] ?? true);
  $has_left = $has_panel_sidebar || $has_sidebar;
@endphp
@once('wds.page.view')
<style>
.wds-layout { display: flex; min-height: 100vh; background: var(--wds-bg); color: var(--wds-text); }
.wds-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.wds-header-heading { font-size: 1.625rem; font-weight: 500; letter-spacing: -0.02em; line-height: 1.2; color: var(--wds-text); }
.wds-header-subheading { margin-top: 0.25rem; font-size: var(--wds-text-sm); color: var(--wds-text-muted); }
.wds-header-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; flex-wrap: wrap; }
.wds-page { display: flex; flex-direction: column; gap: 1.25rem; color: var(--wds-text); }
.wds-page-content { min-width: 0; color: var(--wds-text); }
@media (max-width: 640px) {
  .wds-header-heading { font-size: 1.375rem; }
}
</style>
@endonce
<x-webkernel::page.base :title="$document_title" :lang="$lang" :theme="$theme" :csrf="$csrf" :favicon="$favicon" :description="$document_description" layout="sidebar">
  <script>
  (function(d){var s=localStorage.getItem('wds-sidebar');if(s)d.dataset.wdsSidebar=s;})(document.documentElement);</script>
  <div class="wds-layout" data-wds-sidebar-root>
    @if ($has_left)
      <x-webkernel::sidebar.group position="left">
        @if ($has_panel_sidebar)
          <x-webkernel::rail>
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
    </x-webkernel::main>

    <x-webkernel::aside position="right" collapsed>
      {!! $aside ?? '' !!}
    </x-webkernel::aside>
  </div>
</x-webkernel::page.base>
