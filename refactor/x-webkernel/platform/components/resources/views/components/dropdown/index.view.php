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
@once('wds.dropdown')
<style>
.wds-dropdown { position: relative; display: inline-flex; }
.wds-dropdown-trigger { display: flex; cursor: pointer; }
.wds-dropdown-panel {
  display: none;
  position: absolute; z-index: 20;
  min-width: 14rem; width: max-content; max-width: min(20rem, calc(100vw - 2rem));
  border-radius: var(--wds-radius-lg);
  background: var(--wds-surface);
  box-shadow: 0 10px 15px -3px color-mix(in srgb, var(--wds-text) 10%, transparent), 0 0 0 1px color-mix(in srgb, var(--wds-text) 5%, transparent);
  inset-inline-end: 0; top: calc(100% + 0.5rem);
}
.wds-dropdown.wds-open > .wds-dropdown-panel { display: block; }
.wds-dropdown-panel.wds-scrollable { overflow-y: auto; max-height: min(20rem, 70vh); }
.wds-dropdown-panel.wds-width-xs { max-width: 20rem; }
.wds-dropdown-panel.wds-width-sm { max-width: 24rem; }
.wds-dropdown-panel.wds-width-md { max-width: 28rem; }
.wds-dropdown-panel.wds-width-lg { max-width: 32rem; }
</style>
@endonce
@once('wds.dropdown.js')
<script>
(function () {
  document.addEventListener('click', function (event) {
    document.querySelectorAll('[data-wds-dropdown]').forEach(function (box) {
      if (box.contains(event.target)) {
        if (event.target.closest('[data-wds-dropdown-trigger]')) {
          box.classList.toggle('wds-open');
        }
        return;
      }
      box.classList.remove('wds-open');
    });
  });
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-wds-dropdown].wds-open').forEach(function (box) {
      box.classList.remove('wds-open');
    });
  });
})();
</script>
@endonce
<div {{ $attributes->class('wds-dropdown') }} data-wds-dropdown data-placement="{{ $placement }}">
  <div class="wds-dropdown-trigger" data-wds-dropdown-trigger>
    {!! $trigger ?? '' !!}
  </div>
  <div class="wds-dropdown-panel{{ $width ? ' wds-width-'.$width : '' }}" data-wds-dropdown-panel>
    {!! $slot !!}
  </div>
</div>
