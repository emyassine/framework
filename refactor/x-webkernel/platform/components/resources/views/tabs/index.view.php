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
<style>
.wds-tabs { display: flex; flex-direction: column; gap: 1.25rem; color: var(--wds-text); }
.wds-tabs.wds-vertical { flex-direction: row; align-items: stretch; }
.wds-tabs-bar {
  display: flex; gap: 0.25rem; border-bottom: 1px solid var(--wds-border);
}
.wds-tabs.wds-vertical .wds-tabs-bar {
  flex-direction: column; border-bottom: 0; border-inline-end: 1px solid var(--wds-border);
}
.wds-tabs-item {
  padding: 0.7em 1em; font-weight: 550; font-size: 13px; color: var(--wds-text-muted);
  border-bottom: 2px solid transparent; margin-bottom: -1px; border-radius: 4px 4px 0 0;
  background: transparent;
}
.wds-tabs-item:hover { color: var(--wds-text); }
.wds-tabs-item.wds-active,
.wds-tabs-item[aria-selected="true"] {
  color: var(--wds-text); border-bottom-color: var(--primary-600);
}
.wds-tabs-panel { color: var(--wds-text); }
.wds-tabs-panel[hidden] { display: none; }
</style>
<div class="wds-tabs{{ $contained ? ' wds-contained' : '' }}{{ $vertical ? ' wds-vertical' : '' }}" data-wds-tabs>
  <nav class="wds-tabs-bar" role="tablist" aria-label="{{ $label ?? '' }}">
    {!! $list ?? '' !!}
  </nav>
  {!! $slot !!}
</div>
