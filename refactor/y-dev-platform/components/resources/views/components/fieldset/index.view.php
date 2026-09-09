{{--
  <x-webkernel::fieldset label="Address">…</x-webkernel::fieldset>
--}}
@props([
  'label' => null,
  'contained' => true,
  'label_hidden' => false,
  'required' => false,
  'grid_class' => '',
  'grid_style' => '',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<fieldset {{ $attributes->class([
  'w-fieldset',
  'w-fieldset-label-hidden' => $label_hidden,
  'w-fieldset-not-contained' => ! $contained,
]) }}>
  @if ($label !== null && $label !== '')
    <legend>
      {{ $label }}@if ($required)<sup class="w-fieldset-label-required">*</sup>@endif
    </legend>
  @endif
  <div class="{{ $grid_class !== '' ? $grid_class : '' }}"@if ($grid_style !== '') style="{{ $grid_style }}"@endif>
    {!! $slot !!}
  </div>
</fieldset>
