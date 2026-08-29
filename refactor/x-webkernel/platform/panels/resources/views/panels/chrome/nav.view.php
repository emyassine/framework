@php
  $panel_api = webapp()->panel();
  $current = $panel_api->matching_path();
  $groups = \is_array($current) ? ($current['navigation'] ?? []) : [];
  $label = \is_array($current) ? (string) ($current['label'] ?? '') : ($brand ?? 'Webkernel');
@endphp
<nav class="wds-nav" id="sidebar" aria-label="{{ lang('panel.navigation') }}">
  <a class="wds-nav-brand" href="{{ \is_array($current) ? ($current['href'] ?? '/') : '/' }}">
    <span class="wds-nav-brand-name">{{ $label }}</span>
  </a>
  <div class="wds-nav-scroll">
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
                @include('webkernel::panels.chrome.items', ['items' => $group['items'] ?? [], 'panel_api' => $panel_api])
              </ul>
            </details>
          @else
            <ul>
              @include('webkernel::panels.chrome.items', ['items' => $group['items'] ?? [], 'panel_api' => $panel_api])
            </ul>
          @endif
        </li>
      @endforeach
    </ul>
  </div>
</nav>
