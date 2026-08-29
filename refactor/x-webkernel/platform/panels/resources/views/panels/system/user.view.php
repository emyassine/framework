<div class="webkernel-shell-user-menu" id="webkernel-shell-user-menu">
  <button type="button" class="webkernel-shell-user-menu__trigger" onclick="toggleUserMenu()" aria-haspopup="menu" aria-expanded="false">
    <span class="webkernel-shell-avatar">{{ strtoupper(substr((string) ($brand ?? 'W'), 0, 1)) }}</span>
    <span class="webkernel-shell-sidebar-footer__text">
      <span class="webkernel-shell-sidebar-footer__name">{{ $brand ?? 'Webkernel' }}</span>
      <span class="webkernel-shell-sidebar-footer__role">Signed in</span>
    </span>
    <span class="webkernel-shell-user-menu__chevron">
      <x-webkernel::icon name="chevron-up" />
    </span>
  </button>
  <div class="webkernel-shell-user-menu__panel" role="menu">
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
