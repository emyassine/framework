@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.dropdown.list')
<style>
.wds-dropdown-list { display: grid; gap: 1px; padding: 0.25rem; }
</style>
@endonce
<div {{ $attributes->class('wds-dropdown-list') }} role="menu">
  {!! $slot !!}
</div>
