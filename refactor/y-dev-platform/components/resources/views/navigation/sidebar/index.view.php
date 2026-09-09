@props([
  'open' => true,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<nav {{ $attributes->class('w-nav w-drawer') }} data-w-drawer @if (! $open) hidden @endif aria-label="{{ lang('panel.navigation') }}">
  {!! $slot !!}
</nav>
