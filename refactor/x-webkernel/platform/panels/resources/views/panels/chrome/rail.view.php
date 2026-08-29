@php
  $panel_api = webapp()->panel();
  $apps = $panel_api->all();
  $current_app = $panel_api->matching_path();
  $current_id = \is_array($current_app) ? (string) ($current_app['id'] ?? '') : '';
  $customizable = (bool) \Webkernel\Config\Config::get('ui.sidebar_customizable', false);
@endphp
<nav class="wds-sidebar" id="sidebar" aria-label="Main navigation">
  <ul class="wds-app-list" id="wds-app-list" data-customizable="{{ $customizable ? '1' : '0' }}">
    <li class="wds-app-logo" id="wds-app-logo">
      <a href="/" title="{{ $brand ?? 'Webkernel' }}">
        @if (!empty($logo))
          <img class="wds-logo-img" src="{{ $logo }}" alt="{{ $brand ?? 'Webkernel' }}" width="30" height="30" />
        @else
          <div class="wds-logo-icon">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="8" cy="8" r="6"/>
              <path d="M8 2v6l3 3"/>
            </svg>
          </div>
        @endif
      </a>
    </li>
    @foreach ($apps as $app)
      @php
        $app_id = (string) ($app['id'] ?? '');
        $app_href = (string) ($app['href'] ?? '/'.$app_id);
        $app_label = (string) ($app['label'] ?? \ucfirst($app_id));
        $app_icon = (string) ($app['icon'] ?? 'package');
        $app_active = $app_id !== '' && $app_id === $current_id;
      @endphp
      <li class="wds-app-item{{ $app_active ? ' wds-active' : '' }}" data-app="{{ $app_id }}">
        <a class="wds-app-icon" href="{{ $app_href }}" title="{{ $app_label }}">
          <span class="wds-icon">{!! icon($app_icon, 'wds-icon-svg') !!}</span>
          <span class="wds-app-item-label">{{ $app_label }}</span>
        </a>
        <div class="wds-app-submenu">
          <div class="wds-app-menu-header">{{ $app_label }}</div>
          <div class="wds-sidebar-nav">
            @foreach (($app['navigation'] ?? []) as $group)
              <div class="wds-sidebar-group-label">{{ $group['label'] ?? '' }}</div>
              <div class="wds-sidebar-group">
                @foreach (($group['items'] ?? []) as $item)
                  @php
                    $item_href = (string) ($item['href'] ?? '#');
                    $item_active = $panel_api->href_is_active($item_href);
                  @endphp
                  <a href="{{ $item_href }}" class="wds-sidebar-item{{ $item_active ? ' wds-active' : '' }}">{{ $item['label'] ?? '' }}</a>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>
      </li>
    @endforeach
  </ul>
  @if ($customizable)
    <button type="button" class="wds-app-reorder" id="wds-app-reorder" onclick="toggleAppReorder()" title="Customize sidebar" aria-label="Customize sidebar">
      <span class="wds-icon">{!! icon('grip', 'wds-icon-svg') !!}</span>
    </button>
  @endif
</nav>
