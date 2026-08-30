@once('wds.user-menu')
<style>
.wds-user-menu { position: relative; }
.wds-user-menu-trigger {
  display: flex; align-items: center; gap: 0.5em;
  padding: 0 10px; min-height: 40px; border-radius: 4px;
  color: var(--wds-text);
}
.wds-user-menu-trigger:hover { background: var(--wds-bg-subtle); }
.wds-user-menu-name { font-size: 13px; font-weight: 600; white-space: nowrap; }
.wds-user-menu-chevron { width: 14px; height: 14px; color: var(--wds-text-muted); opacity: 0.5; }
.wds-user-menu-panel {
  display: none; position: absolute; inset-inline-end: 0; top: calc(100% + 0.5rem);
  min-width: 180px; background: var(--wds-surface); border: 1px solid var(--wds-border);
  border-radius: 8px; padding: 4px; z-index: 60;
}
.wds-user-menu.wds-open .wds-user-menu-panel { display: flex; flex-direction: column; }
.wds-user-menu-panel a,
.wds-user-menu-panel button {
  display: flex; align-items: center; gap: 0.5rem; padding: 0.6em 0.9em;
  border-radius: 4px; color: var(--wds-text-muted); font-size: 13px; font-weight: 500;
  width: 100%; background: none; border: none; cursor: pointer; font: inherit; text-align: start;
}
.wds-user-menu-panel a:hover,
.wds-user-menu-panel button:hover { background: var(--wds-bg-subtle); color: var(--wds-text); }
@media (max-width: 640px) {
  .wds-user-menu-name { display: none; }
}
</style>
@endonce
@once('wds.user-menu.js')
<script>
(function () {
  window.toggleUserMenu = function () {
    var el = document.getElementById('wds-user-menu');
    if (el) el.classList.toggle('wds-open');
  };
  document.addEventListener('click', function (event) {
    var el = document.getElementById('wds-user-menu');
    if (el && !el.contains(event.target)) {
      el.classList.remove('wds-open');
    }
  });
})();
</script>
@endonce
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
    <form method="post" action="/logout" role="none">
      @csrf
      <button type="submit" role="menuitem">
        <span class="wds-icon">{!! icon('log-out', 'wds-icon-svg') !!}</span>
        {{ lang('panel.sign_out') }}
      </button>
    </form>
  </div>
</div>
