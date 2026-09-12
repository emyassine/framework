<div class="w-user-menu" id="w-user-menu">
  <button type="button" class="w-user-menu-trigger" onclick="toggleUserMenu()" aria-haspopup="menu" aria-expanded="false">
    <span class="w-avatar">{{ strtoupper(substr((string) ($brand ?? 'W'), 0, 1)) }}</span>
    <span class="w-user-menu-name">{{ $brand ?? 'Webkernel' }}</span>
    <span class="w-user-menu-chevron">
      <span class="w-icon">{!! icon('chevron-down', 'w-icon-svg') !!}</span>
    </span>
  </button>
  <div class="w-user-menu-panel" role="menu">
    <a href="/system" role="menuitem">
      <span class="w-icon">{!! icon('circle-user', 'w-icon-svg') !!}</span>
      {{ lang('panel.profile') }}
    </a>
    <form method="post" action="/logout" role="none">
      @csrf
      <button type="submit" role="menuitem">
        <span class="w-icon">{!! icon('log-out', 'w-icon-svg') !!}</span>
        {{ lang('panel.sign_out') }}
      </button>
    </form>
  </div>
</div>
