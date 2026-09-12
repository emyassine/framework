@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div {{ $attributes->class('w-dropdown-list') }} role="menu">
  {!! $slot !!}
</div>
