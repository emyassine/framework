@props([
  'position' => 'right',
  'collapsed' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<aside
  {{ $attributes->class('w-aside') }}
  data-position="{{ $position }}"
  data-collapsed="{{ $collapsed ? '1' : '0' }}"
>
  {!! $slot !!}
</aside>
