@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.rail')
<style>
.wds-rail {
  width: var(--wds-rail-width);
  flex: 0 0 var(--wds-rail-width);
  height: 100%;
  background: var(--wds-rail-bg);
  display: flex;
  flex-direction: column;
  align-items: center;
}
.wds-rail-logo {
  height: 62px;
  width: 100%;
  display: grid;
  place-content: center;
}
.wds-rail-logo img { width: 30px; height: 30px; object-fit: contain; }
.wds-rail-list { flex: 1; width: 100%; overflow-y: auto; overscroll-behavior: contain; }
.wds-logo-icon {
  width: 28px; height: 28px; border-radius: var(--wds-radius);
  background: var(--primary-600); color: var(--color-white);
  display: grid; place-content: center;
}
@media (max-width: 979px) {
  .wds-rail { display: none; }
  [data-wds-sidebar="expanded"] .wds-rail {
    display: flex; position: fixed; top: 0; bottom: 0; inset-inline-start: 0; z-index: 80;
  }
}
</style>
@endonce
<aside {{ $attributes->class('wds-rail') }} aria-label="{{ lang('panel.apps') }}">
  {!! $slot !!}
</aside>
