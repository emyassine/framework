{{--
  <x-webkernel::select name="status">
    <option value="open">Open</option>
  </x-webkernel::select>
--}}
@props([
  'name' => '',
  'disabled' => false,
  'inline_prefix' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.select')
<style>
select.wds-select-input {
  display: block; width: 100%; appearance: none;
  border: none; background-color: transparent;
  padding: 0.375rem 2rem 0.375rem 0.75rem;
  font: inherit; font-size: var(--wds-text-sm); line-height: 1.5;
  color: var(--wds-text); text-align: start;
  outline: none;
  background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
}
[dir="rtl"] select.wds-select-input {
  padding: 0.375rem 0.75rem 0.375rem 2rem;
  background-position: left 0.5rem center;
}
select.wds-select-input:disabled { color: var(--wds-text-muted); }
select.wds-select-input.wds-select-input-has-inline-prefix { padding-inline-start: 0; }
select.wds-select-input option, select.wds-select-input optgroup {
  background: var(--wds-surface); color: var(--wds-text);
}
@supports (-webkit-touch-callout: none) {
  select.wds-select-input { font-size: 1rem; }
}
</style>
@endonce
<select
  name="{{ $name }}"
  @if ($disabled) disabled @endif
  {{ $attributes->class([
    'wds-select-input',
    'wds-select-input-has-inline-prefix' => $inline_prefix,
  ]) }}
>
  {!! $slot !!}
</select>
