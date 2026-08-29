@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<style>
.wds-main-ctn { flex: 1 1 100%; min-width: 0; display: flex; flex-direction: column; }
.wds-main {
  flex: 1; padding: 25px 20px 20px; width: 100%;
  display: flex; flex-direction: column; gap: 1em; color: var(--wds-text);
}
</style>
<div {{ $attributes->class('wds-main-ctn') }}>
  {!! $slot !!}
</div>
