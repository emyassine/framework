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
<select
  name="{{ $name }}"
  @if ($disabled) disabled @endif
  {{ $attributes->class([
    'w-select-input',
    'w-select-input-has-inline-prefix' => $inline_prefix,
  ]) }}
>
  {!! $slot !!}
</select>
