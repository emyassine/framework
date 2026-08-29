@php
  $panel_api = webapp()->panel();
  $apps = $panel_api->all();
  $current = $panel_api->matching_path();
  $current_id = \is_array($current) ? (string) ($current['id'] ?? '') : '';
@endphp
<aside class="wds-rail" aria-label="{{ lang('panel.apps') }}">
  <a class="wds-rail-logo" href="/" title="{{ $brand ?? 'Webkernel' }}">
    @if (!empty($logo))
      <img src="{{ $logo }}" alt="" width="30" height="30" />
    @else
      <span class="wds-logo-icon">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round">
          <circle cx="8" cy="8" r="6"/><path d="M8 2v6l3 3"/>
        </svg>
      </span>
    @endif
  </a>
  <ul class="wds-rail-list">
    @foreach ($apps as $app)
      @php
        $app_id = (string) ($app['id'] ?? '');
        $app_href = (string) ($app['href'] ?? '/'.$app_id);
        $app_label = (string) ($app['label'] ?? \ucfirst($app_id));
        $app_icon = (string) ($app['icon'] ?? 'package');
        $app_logo = (string) \Webkernel\Config\Config::get('panels.'.$app_id.'.logo', '');
        $app_shape = (string) \Webkernel\Config\Config::get('panels.'.$app_id.'.logo_shape', 'favicon');
        if ($app_shape !== 'round' && $app_shape !== 'square' && $app_shape !== 'favicon') {
          $app_shape = 'favicon';
        }
        $active = $app_id !== '' && $app_id === $current_id;
      @endphp
      <li class="wds-rail-item{{ $active ? ' wds-active' : '' }}">
        <a href="{{ $app_href }}" title="{{ $app_label }}">
          @if ($app_logo !== '')
            <span class="wds-nav-logo--{{ $app_shape }}"><img src="{{ $app_logo }}" alt="" /></span>
          @else
            <span class="wds-icon">{!! icon($app_icon, 'wds-icon-svg') !!}</span>
          @endif
        </a>
      </li>
    @endforeach
  </ul>
</aside>
