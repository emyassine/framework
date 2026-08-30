{{--
  <x-webkernel::topbar :breadcrumbs="$breadcrumbs" />
--}}
@props([
  'breadcrumbs' => [],
  'brand' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $breadcrumbs = \is_array($breadcrumbs) ? $breadcrumbs : [];
@endphp
@once('wds.topbar')
<style>
.wds-topbar {
  height: var(--wds-topbar-height);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0 1rem;
  border-bottom: 1px solid var(--wds-border);
  background: var(--wds-surface);
  color: var(--wds-text);
  position: sticky;
  top: 0;
  z-index: 40;
}
.wds-topbar-end {
  margin-inline-start: auto;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.wds-user-menu { position: relative; }
.wds-user-menu-trigger {
  display: flex; align-items: center; gap: 0.5rem;
  color: var(--wds-text); padding: 0.25rem 0.4rem;
  border-radius: var(--wds-radius);
}
.wds-user-menu-trigger:hover { background: var(--wds-bg-subtle); }
.wds-user-menu-name { font-size: 13px; font-weight: 600; white-space: nowrap; }
.wds-user-menu-chevron { width: 14px; height: 14px; color: var(--wds-text-muted); opacity: 0.5; }
.wds-user-menu-panel {
  display: none; position: absolute; inset-inline-end: 0; top: calc(100% + 0.4rem);
  min-width: 12rem; background: var(--wds-surface); border: 1px solid var(--wds-border);
  border-radius: 8px; z-index: 70; padding: 4px;
}
.wds-user-menu.wds-open .wds-user-menu-panel { display: flex; flex-direction: column; }
.wds-user-menu-panel a {
  display: flex; align-items: center; gap: 0.6em;
  padding: 0.55em 0.75em; border-radius: 6px; color: var(--wds-text);
}
.wds-user-menu-panel a:hover { background: var(--wds-bg-subtle); }
@media (max-width: 640px) {
  .wds-topbar { padding: 0 0.75rem; gap: 0.5rem; }
  .wds-user-menu-name { display: none; }
}
</style>
@endonce
<header {{ $attributes->class('wds-topbar') }}>
  <x-webkernel::icon-button
    icon="menu"
    :label="lang('panel.toggle_sidebar')"
    size="sm"
    color="gray"
    onclick="toggleSidebar()"
  />
  <x-webkernel::breadcrumbs :breadcrumbs="$breadcrumbs" />
  <div class="wds-topbar-end">
    <x-webkernel::language-selector />
    <button type="button" class="wds-icon-btn" onclick="toggleTheme()" title="{{ lang('panel.theme') }}" id="theme-btn">
      <svg id="icon-sun" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="3"/><line x1="8" y1="1" x2="8" y2="3"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="1" y1="8" x2="3" y2="8"/><line x1="13" y1="8" x2="15" y2="8"/><line x1="3.2" y1="3.2" x2="4.6" y2="4.6"/><line x1="11.4" y1="11.4" x2="12.8" y2="12.8"/><line x1="12.8" y1="3.2" x2="11.4" y2="4.6"/><line x1="4.6" y1="11.4" x2="3.2" y2="12.8"/></svg>
      <svg id="icon-moon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;"><path d="M12 10A6 6 0 0 1 6 4a6.003 6 0 0 0 6 9 6 6 0 0 1 0-3z"/></svg>
    </button>
    @include('webkernel::system.user', ['brand' => $brand])
    {!! $slot !!}
  </div>
</header>
