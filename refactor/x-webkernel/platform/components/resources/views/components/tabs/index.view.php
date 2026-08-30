{{--
  <x-webkernel::tabs>
    <x-webkernel::tabs.item tab="branding" :active="true">Branding</x-webkernel::tabs.item>
    <x-webkernel::tabs.panel tab="branding">...</x-webkernel::tabs.panel>
  </x-webkernel::tabs>
--}}
@props([
  'label' => null,
  'contained' => false,
  'vertical' => false,
])
<div class="w-tabs{{ $contained ? ' w-contained' : '' }}{{ $vertical ? ' w-vertical' : '' }}" data-w-tabs>
  <nav class="w-tabs-bar" role="tablist" aria-label="{{ $label ?? '' }}">
    {!! $list ?? '' !!}
  </nav>
  {!! $slot !!}
</div>
