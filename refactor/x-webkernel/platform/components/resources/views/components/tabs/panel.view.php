@props([
  'tab' => '',
  'active' => false,
])
<section class="w-tabs-panel" role="tabpanel" data-tab-panel="{{ $tab }}" @if (! $active) hidden @endif>
  {!! $slot !!}
</section>
