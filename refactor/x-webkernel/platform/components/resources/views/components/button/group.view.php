{{--
  <x-webkernel::button.group>
    <x-webkernel::button>One</x-webkernel::button>
    <x-webkernel::button>Two</x-webkernel::button>
  </x-webkernel::button.group>
--}}
@props([])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.btn.group')
<style>
.wds-btn-group { display: inline-flex; align-items: stretch; }
.wds-btn-group > .wds-btn { border-radius: 0; }
.wds-btn-group > .wds-btn:first-child { border-top-left-radius: var(--wds-radius); border-bottom-left-radius: var(--wds-radius); }
.wds-btn-group > .wds-btn:last-child { border-top-right-radius: var(--wds-radius); border-bottom-right-radius: var(--wds-radius); }
.wds-btn-group > .wds-btn + .wds-btn { margin-inline-start: -1px; }
</style>
@endonce
<div {{ $attributes->class('wds-btn-group') }}>
  {!! $slot !!}
</div>
