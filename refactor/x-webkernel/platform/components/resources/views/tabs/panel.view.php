@props([
  'tab' => '',
  'active' => false,
])
@once('wds.tabs.panel')
<style>
.wds-tabs-panel { color: var(--wds-text); min-width: 0; }
.wds-tabs-panel[hidden] { display: none; }
.wds-tabs.wds-contained .wds-tabs-panel { padding: 0.75rem; }
</style>
@endonce
<section class="wds-tabs-panel" role="tabpanel" data-tab-panel="{{ $tab }}" @if (! $active) hidden @endif>
  {!! $slot !!}
</section>
