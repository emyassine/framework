@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<aside {{ $attributes->class('w-rail') }}
  style="border-right: 1px solid color-mix(in srgb, var(--w-border) 65%, transparent);"
  aria-label="{{ lang('panel.apps') }}">
  {!! $slot !!}
</aside>
