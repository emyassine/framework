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
<div class="wds-tabs{{ $contained ? ' wds-contained' : '' }}{{ $vertical ? ' wds-vertical' : '' }}" data-wds-tabs>
  <nav class="wds-tabs-bar" role="tablist" aria-label="{{ $label ?? '' }}">
    {!! $list ?? '' !!}
  </nav>
  {!! $slot !!}
</div>
