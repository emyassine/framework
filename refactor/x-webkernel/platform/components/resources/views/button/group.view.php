{{--
  <x-webkernel::button.group>
    <x-webkernel::button>One</x-webkernel::button>
    <x-webkernel::button>Two</x-webkernel::button>
  </x-webkernel::button.group>
--}}
@props([])
@include('webkernel::wds.btn')
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div {{ $attributes->class('wds-btn-group') }}>
  {!! $slot !!}
</div>
