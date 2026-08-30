@props([
  'position' => 'right',
  'collapsed' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.page.aside.view')
<style>
.wds-aside {
  flex: 0 0 var(--wds-aside-width, 20rem);
  min-width: 0;
  background: var(--wds-surface);
  color: var(--wds-text);
  border-inline-start: 1px solid var(--wds-border);
  padding: 1.25rem;
}
.wds-aside[data-collapsed="1"] { display: none; }
</style>
@endonce
<aside
  {{ $attributes->class('wds-aside') }}
  data-position="{{ $position }}"
  data-collapsed="{{ $collapsed ? '1' : '0' }}"
>
  {!! $slot !!}
</aside>
