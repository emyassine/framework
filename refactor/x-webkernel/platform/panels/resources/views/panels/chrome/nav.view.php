@php
  $panel_api = webapp()->panel();
  $apps = $panel_api->all();
  $customizable = (bool) \Webkernel\Config\Config::get('ui.sidebar_customizable', false);
@endphp
<nav class="wds-nav" id="sidebar" aria-label="{{ lang('panel.navigation') }}">
  <a class="wds-nav-brand" href="/" title="{{ $brand ?? 'Webkernel' }}">
    <span class="wds-nav-brand-mark">
      @if (!empty($logo))
        <img src="{{ $logo }}" alt="" width="28" height="28" />
      @else
        <span class="wds-logo-icon">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round">
            <circle cx="8" cy="8" r="6"/><path d="M8 2v6l3 3"/>
          </svg>
        </span>
      @endif
    </span>
    <span class="wds-nav-brand-name">{{ $brand ?? 'Webkernel' }}</span>
  </a>

  <div class="wds-nav-scroll">
    <label class="wds-nav-search">
      <span class="wds-nav-icon">{!! icon('search', 'wds-icon-svg') !!}</span>
      <input type="search" placeholder="{{ lang('panel.search') }}" autocomplete="off" />
    </label>

    <ul class="wds-nav-list" id="wds-nav-list" data-customizable="{{ $customizable ? '1' : '0' }}">
      @foreach ($apps as $app)
        @php
          $app_id = (string) ($app['id'] ?? '');
          $app_label = (string) ($app['label'] ?? \ucfirst($app_id));
          $app_logo = (string) \Webkernel\Config\Config::get('panels.'.$app_id.'.logo', '');
          $app_shape = (string) \Webkernel\Config\Config::get('panels.'.$app_id.'.logo_shape', 'favicon');
          if ($app_shape !== 'round' && $app_shape !== 'square' && $app_shape !== 'favicon') {
            $app_shape = 'favicon';
          }
        @endphp
        <li data-app="{{ $app_id }}">
          <div class="wds-nav-section">
            @if ($app_logo !== '')
              <span class="wds-nav-section-mark wds-nav-logo--{{ $app_shape }}">
                <img src="{{ $app_logo }}" alt="" />
              </span>
            @endif
            <span>{{ $app_label }}</span>
          </div>
          <ul>
            @foreach (($app['navigation'] ?? []) as $group)
              @foreach (($group['items'] ?? []) as $item)
                @php
                  $item_href = (string) ($item['href'] ?? '#');
                  $item_label = (string) ($item['label'] ?? '');
                  if (\str_starts_with($item_label, 'panel.')) {
                    $item_label = lang($item_label);
                  }
                  $item_icon = (string) ($item['icon'] ?? ($app['icon'] ?? 'package'));
                  $item_active = $panel_api->href_is_active($item_href);
                @endphp
                <li>
                  <a href="{{ $item_href }}" class="wds-nav-link{{ $item_active ? ' wds-active' : '' }}"@if ($item_active) aria-current="page"@endif>
                    <span class="wds-nav-icon">{!! icon($item_icon, 'wds-icon-svg') !!}</span>
                    <span>{{ $item_label }}</span>
                  </a>
                </li>
              @endforeach
            @endforeach
          </ul>
        </li>
      @endforeach
    </ul>
  </div>
</nav>
