@props([
  'label' => '',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<section {{ $attributes->class(['w-wizard-panel']) }} role="group" aria-label="{{ $label }}">
  {!! $slot !!}
</section>
