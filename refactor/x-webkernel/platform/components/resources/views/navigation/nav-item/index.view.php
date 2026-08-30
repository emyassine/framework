@props([
  'href' => '#',
  'icon' => null,
  'active' => false,
])
@once('wds.nav-item')
<style>
.wds-sidebar-item {
  display: flex;
  align-items: center;
  gap: 0.6em;
  padding: 0.65em 1.3em;
  width: 100%;
  color: var(--wds-text);
  font-weight: 500;
  border-radius: var(--wds-radius);
}
.wds-sidebar-item:hover { background: var(--wds-bg-subtle); }
.wds-sidebar-item.wds-active { background: var(--primary-600); color: var(--color-white); }
.wds-sidebar-item-label { min-width: 0; }
</style>
@endonce
<a href="{{ $href }}" class="wds-sidebar-item{{ !empty($active) ? ' wds-active' : '' }}">
  @if (!empty($icon))
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="wds-sidebar-item-label">{!! $slot !!}</span>
</a>
