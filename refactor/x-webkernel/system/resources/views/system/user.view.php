<div class="wds-user-menu" id="wds-user-menu">
  <button type="button" class="wds-user-menu-trigger" onclick="toggleUserMenu()" aria-haspopup="menu" aria-expanded="false">
    <span class="wds-avatar">{{ strtoupper(substr((string) ($brand ?? 'W'), 0, 1)) }}</span>
    <span class="wds-user-menu-name">{{ $brand ?? 'Webkernel' }}</span>
    <span class="wds-user-menu-chevron">
      <span class="wds-icon">{!! icon('chevron-down', 'wds-icon-svg') !!}</span>
    </span>
  </button>
  <div class="wds-user-menu-panel" role="menu">
    <a href="/system" role="menuitem">
      <span class="wds-icon">{!! icon('circle-user', 'wds-icon-svg') !!}</span>
      {{ lang('panel.profile') }}
    </a>
    <a href="/" role="menuitem">
      <span class="wds-icon">{!! icon('log-out', 'wds-icon-svg') !!}</span>
      {{ lang('panel.sign_out') }}
    </a>
  </div>
</div>
