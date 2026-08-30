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
@once('wds.tabs')
<style>
.wds-tabs { display: flex; flex-direction: column; gap: 1.25rem; color: var(--wds-text); }
.wds-tabs.wds-vertical { flex-direction: row; align-items: stretch; gap: 1rem; }
.wds-tabs-bar {
  display: flex; gap: 0.25rem; border-bottom: 1px solid var(--wds-border);
}
.wds-tabs.wds-vertical .wds-tabs-bar {
  flex-direction: column; border-bottom: 0; border-inline-end: 1px solid var(--wds-border);
}
.wds-tabs.wds-contained {
  background: var(--wds-surface);
  border: 1px solid var(--wds-border);
  border-radius: var(--wds-radius);
  gap: 0;
  overflow: hidden;
}
.wds-tabs.wds-contained .wds-tabs-bar {
  border-bottom-color: var(--wds-border);
  padding: 0.35rem;
  gap: 0.15rem;
  background: var(--wds-bg-subtle);
}
.wds-tabs.wds-contained.wds-vertical .wds-tabs-bar {
  border-bottom: 0;
  border-inline-end: 1px solid var(--wds-border);
}
</style>
@endonce
@once('wds.tabs.js')
<script>
document.addEventListener('click', function (event) {
  var tab = event.target.closest('[data-wds-tabs] [role="tab"]');
  if (!tab) return;
  var root = tab.closest('[data-wds-tabs]');
  var id = tab.getAttribute('data-tab');
  root.querySelectorAll('[role="tab"]').forEach(function (other) {
    var on = other === tab;
    other.classList.toggle('wds-active', on);
    other.setAttribute('aria-selected', on ? 'true' : 'false');
  });
  root.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
    panel.hidden = panel.getAttribute('data-tab-panel') !== id;
  });
});
</script>
@endonce
<div class="wds-tabs{{ $contained ? ' wds-contained' : '' }}{{ $vertical ? ' wds-vertical' : '' }}" data-wds-tabs>
  <nav class="wds-tabs-bar" role="tablist" aria-label="{{ $label ?? '' }}">
    {!! $list ?? '' !!}
  </nav>
  {!! $slot !!}
</div>
