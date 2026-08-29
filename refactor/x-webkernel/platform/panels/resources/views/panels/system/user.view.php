<div class="wds-user-menu" id="wds-user-menu">
  <button type="button" class="wds-user-menu-trigger" onclick="toggleUserMenu()" aria-haspopup="menu" aria-expanded="false">
    <span class="wds-avatar">{{ strtoupper(substr((string) ($brand ?? 'W'), 0, 1)) }}</span>
    <span class="wds-sidebar-footer-text">
      <span class="wds-sidebar-footer-name">{{ $brand ?? 'Webkernel' }}</span>
      <span class="wds-sidebar-footer-role">Signed in</span>
    </span>
    <span class="wds-user-menu-chevron">
      <x-webkernel::icon name="chevron-up" />
    </span>
  </button>
  <div class="wds-user-menu-panel" role="menu">
    <a href="/system" role="menuitem">
      <x-webkernel::icon name="circle-user" />
      Profile
    </a>
    <a href="/system" role="menuitem">
      <x-webkernel::icon name="settings" />
      Settings
    </a>
    <a href="/" role="menuitem">
      <x-webkernel::icon name="log-out" />
      Sign out
    </a>
  </div>
</div>
