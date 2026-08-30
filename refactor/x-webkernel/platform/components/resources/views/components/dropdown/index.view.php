{{--
  <x-webkernel::dropdown>
    <x-slot name="trigger"><x-webkernel::icon-button icon="ellipsis" /></x-slot>
    <x-webkernel::dropdown.list>…</x-webkernel::dropdown.list>
  </x-webkernel::dropdown>
--}}
@props([
  'placement' => 'bottom-end',
  'width' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div {{ $attributes->class('w-dropdown') }} data-w-dropdown data-placement="{{ $placement }}">
  <div class="w-dropdown-trigger" data-w-dropdown-trigger>
    {!! $trigger ?? '' !!}
  </div>
  <div class="w-dropdown-panel{{ $width ? ' w-width-'.$width : '' }}" data-w-dropdown-panel>
    {!! $slot !!}
  </div>
</div>
