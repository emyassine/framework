@props([
  'position' => 'left',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.sidebar.group')
<style>
.wds-sidebar-group {
  display: flex;
  flex: 0 0 auto;
  position: sticky;
  top: 0;
  height: 100vh;
  height: 100dvh;
  z-index: 50;
}
</style>
@endonce
<div {{ $attributes->class('wds-sidebar-group') }} data-position="{{ $position }}">
  {!! $slot !!}
</div>
