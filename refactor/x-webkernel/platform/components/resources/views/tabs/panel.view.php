@props([
  'tab' => '',
  'active' => false,
])
<section class="wds-tabs-panel" role="tabpanel" data-tab-panel="{{ $tab }}" @if (! $active) hidden @endif>
  {!! $slot !!}
</section>
