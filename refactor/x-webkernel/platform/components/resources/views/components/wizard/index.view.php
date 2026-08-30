{{--
  <x-webkernel::wizard>…</x-webkernel::wizard>
--}}
@props([
  'label' => null,
  'list' => '',
  'slot' => '',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div {{ $attributes->class(['w-wizard']) }} data-w-wizard>
  <ol class="w-wizard-nav" aria-label="{{ $label ?? '' }}">
    {!! $list !!}
  </ol>
  <div class="w-wizard-panels">
    {!! $slot !!}
  </div>
  <div class="w-wizard-controls">
    <button type="button" class="w-btn w-ghost" data-wizard-prev>Back</button>
    <button type="button" class="w-btn w-color-primary" data-wizard-next>Next</button>
  </div>
</div>
