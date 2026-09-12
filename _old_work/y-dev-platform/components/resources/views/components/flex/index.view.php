{{--
  <x-webkernel::flex from="md">…</x-webkernel::flex>
--}}
@props([
  'from' => 'md',
  'dense' => false,
  'gap' => true,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div
  {{ $attributes->class(['w-flex', 'w-dense' => $dense, 'w-no-gap' => ! $gap])->merge(['data-from' => $from]) }}
>
  {!! $slot !!}
</div>
