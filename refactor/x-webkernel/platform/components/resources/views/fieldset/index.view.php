{{--
  <x-webkernel::fieldset label="Address">…</x-webkernel::fieldset>
--}}
@props([
  'label' => null,
  'contained' => true,
  'label_hidden' => false,
  'required' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.fieldset')
<style>
.wds-fieldset:not(.wds-fieldset-not-contained) {
  border-radius: var(--wds-radius-lg);
  border: 1px solid var(--wds-border);
  padding: 1.5rem;
  color: var(--wds-text);
}
.wds-fieldset.wds-fieldset-not-contained { padding-top: 1.5rem; border: 0; }
.wds-fieldset > legend {
  margin-inline-start: -0.5rem; padding-inline: 0.5rem;
  font-size: var(--wds-text-sm); line-height: 1.5; font-weight: 500;
  color: var(--wds-text);
}
.wds-fieldset-label-required { color: var(--danger-600); font-weight: 500; }
.wds-fieldset.wds-fieldset-label-hidden > legend {
  position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
  overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
}
</style>
@endonce
<fieldset {{ $attributes->class([
  'wds-fieldset',
  'wds-fieldset-label-hidden' => $label_hidden,
  'wds-fieldset-not-contained' => ! $contained,
]) }}>
  @if ($label !== null && $label !== '')
    <legend>
      {{ $label }}@if ($required)<sup class="wds-fieldset-label-required">*</sup>@endif
    </legend>
  @endif
  {!! $slot !!}
</fieldset>
