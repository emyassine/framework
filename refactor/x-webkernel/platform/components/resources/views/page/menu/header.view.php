@props([
  'title' => '',
])
@once('wds.page.menu.header')
<style>
.wds-drawer-header {
  flex: 0 0 auto;
  height: 62px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: color-mix(in srgb, currentColor 15%, transparent) 0 1px 0;
  color: inherit;
  overflow: hidden;
}
.wds-drawer-title {
  font-size: 16px;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: inherit;
}
</style>
@endonce
<div class="wds-drawer-header">
  <span class="wds-drawer-title">{{ $title }}</span>
</div>
