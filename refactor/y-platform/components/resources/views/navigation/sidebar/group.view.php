@props([
  'position' => 'left',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div {{ $attributes->class('w-sidebar-group') }} data-position="{{ $position }}">
  {!! $slot !!}
</div>
