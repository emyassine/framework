@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.rail')
<style>
.wds-rail {
  width: var(--wds-rail-width);
  flex: 0 0 var(--wds-rail-width);
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: var(--wds-surface);
  color: var(--wds-text);
  box-shadow: 0 20px 25px -5px color-mix(in srgb, var(--gray-950) 8%, transparent);
  box-shadow: 0 0 0 1px color-mix(in srgb, var(--gray-950) 5%, transparent);
  transition: background var(--wds-transition), box-shadow var(--wds-transition);
}
.wds-rail-brand {
  width: 100%;
  height: 4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 0.35rem;
  flex-shrink: 0;
}
.wds-rail-logo {
  display: grid;
  place-content: center;
  text-decoration: none;
  color: inherit;
}
.wds-rail-logo-mark {
  display: grid;
  place-content: center;
  padding: 0.5rem;
  border-radius: var(--wds-radius-lg);
  background: color-mix(in oklch, var(--primary-200) 78%, white);
}
.wds-rail-logo-mark img {
  width: 1.5rem;
  height: 1.5rem;
  object-fit: contain;
  display: block;
}
.wds-logo-icon {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: var(--wds-radius);
  background: var(--primary-600);
  color: var(--color-white);
  display: grid;
  place-content: center;
}
.wds-rail-account {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0;
  border-block: 1px solid var(--wds-border);
  flex-shrink: 0;
}
.wds-rail-avatar-btn {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: var(--wds-radius-lg);
  display: grid;
  place-content: center;
  color: var(--wds-text-muted);
  background: var(--gray-200);
  text-decoration: none;
  transition: background var(--wds-transition), color var(--wds-transition), box-shadow var(--wds-transition);
}
.wds-rail-avatar-btn:hover {
  background: var(--gray-300);
  color: var(--wds-text);
}
.wds-rail-avatar-btn .wds-icon,
.wds-rail-avatar-btn svg {
  width: 1.1rem;
  height: 1.1rem;
}
.wds-rail-avatar-btn.wds-outlined {
  background: transparent;
  box-shadow: 0 0 0 2px var(--wds-border);
}
.wds-rail-avatar-btn.wds-outlined:hover {
  box-shadow: 0 0 0 2px var(--primary-300);
  background: transparent;
}
.wds-rail-list {
  flex: 1;
  width: 100%;
  overflow-y: auto;
  overscroll-behavior: contain;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 0 1.25rem;
}
.wds-rail-item { width: 100%; display: flex; justify-content: center; }
.wds-rail-item > a,
.wds-rail-item > button {
  display: grid;
  place-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: var(--wds-radius-lg);
  color: var(--wds-text-muted);
  background: var(--gray-100);
  position: relative;
  text-decoration: none;
  transition: background var(--wds-transition), color var(--wds-transition), box-shadow var(--wds-transition);
}
.wds-rail-item > a:hover,
.wds-rail-item > button:hover {
  color: var(--wds-text);
  background: var(--gray-200);
}
.wds-rail-item.wds-active > a,
.wds-rail-item.wds-active > button {
  color: var(--primary-700);
  background: var(--primary-50);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary-500) 50%, transparent);
}
.wds-rail-item svg { width: 1.15rem; height: 1.15rem; stroke: currentColor; fill: none; stroke-width: 1.75; }
.wds-rail-item img { width: 1.5rem; height: 1.5rem; display: block; object-fit: contain; }
.wds-rail-item .wds-nav-logo--round img { border-radius: 50%; object-fit: cover; width: 2.5rem; height: 2.5rem; }
.wds-rail-item .wds-nav-logo--square img { border-radius: var(--wds-radius-lg); object-fit: cover; width: 2.5rem; height: 2.5rem; }
.wds-rail-item .wds-nav-logo--favicon img { width: 1.15rem; height: 1.15rem; object-fit: contain; }
@media (min-width: 980px) {
  .wds-rail {
    background: transparent;
    box-shadow: none;
  }
}
@media (max-width: 979px) {
  .wds-rail { display: none; }
  [data-wds-sidebar="expanded"] .wds-rail {
    display: flex;
    position: fixed;
    top: 0;
    bottom: 0;
    inset-inline-start: 0;
    z-index: 80;
    background: var(--wds-surface);
    box-shadow: 0 20px 25px -5px color-mix(in srgb, var(--gray-950) 8%, transparent);
  }
}
</style>
@endonce
<aside {{ $attributes->class('wds-rail') }} aria-label="{{ lang('panel.apps') }}">
  {!! $slot !!}
</aside>
