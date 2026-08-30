@props([
  'label' => '',
  'href' => '#',
  'icon' => null,
  'active' => false,
])
@once('wds.page.menu.element')
<style>
.wds-menu-element, .wds-nav-link {
  margin: 0;
  padding: 0.65em 1.3em;
  width: 100%;
  display: flex;
  align-items: center;
  color: inherit;
  font-weight: 500;
  border-radius: var(--wds-radius);
  text-align: start;
}
.wds-menu-element:hover, .wds-nav-link:hover {
  background: color-mix(in srgb, currentColor 10%, transparent);
}
.wds-menu-element.wds-active, .wds-nav-link.wds-active, .wds-nav-link[aria-current="page"] {
  background: var(--primary-600);
  color: var(--color-white);
}
.wds-nav-icon {
  flex: 0 0 1.3em;
  text-align: center;
  margin-inline-end: 0.8em;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.wds-nav-icon .wds-icon, .wds-nav-icon svg { opacity: 0.4; }
.wds-menu-element:hover .wds-nav-icon .wds-icon,
.wds-menu-element.wds-active .wds-nav-icon .wds-icon,
.wds-nav-link:hover .wds-nav-icon svg,
.wds-nav-link.wds-active .wds-nav-icon svg { opacity: 1; }
</style>
@endonce
<a href="{{ $href }}" class="wds-menu-element wds-nav-link{{ $active ? ' wds-active' : '' }}"{!! $active ? ' aria-current="page"' : '' !!}>
  @if (!empty($icon))
    <span class="wds-nav-icon" aria-hidden="true"><x-webkernel::icon :name="$icon" /></span>
  @endif
  <span>{{ $label }}</span>
</a>
