{{--
  <x-webkernel::section heading="Branding" description="...">
    fields
  </x-webkernel::section>
--}}
@props([
  'heading' => null,
  'description' => null,
  'contained' => true,
  'compact' => false,
  'icon' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
@once('wds.section')
<style>
.wds-section {
  overflow: hidden;
  color: var(--wds-text);
}
.wds-section:not(.wds-section-not-contained) {
  border-radius: 0.75rem;
  background: var(--wds-surface);
  box-shadow: 0 1px 2px color-mix(in srgb, var(--wds-text) 6%, transparent), 0 0 0 1px color-mix(in srgb, var(--wds-text) 5%, transparent);
}
.wds-section.wds-compact:not(.wds-section-not-contained) { border-radius: var(--wds-radius-lg); }
.wds-section-header { display: flex; align-items: flex-start; gap: 0.75rem; padding: 1rem 1.5rem; }
.wds-section.wds-compact .wds-section-header { padding: 0.625rem 1rem; }
.wds-section-header-text { display: grid; flex: 1; row-gap: 0.25rem; }
.wds-section-heading { font-size: var(--wds-text-md); line-height: 1.5; font-weight: 600; color: var(--wds-text); }
.wds-section-description { overflow: hidden; font-size: var(--wds-text-sm); color: var(--wds-text-muted); overflow-wrap: anywhere; }
.wds-section-content-ctn { border-top: 1px solid var(--wds-border); }
.wds-section-not-contained .wds-section-content-ctn { border-top: 0; }
.wds-section-body { padding: 1.5rem; color: var(--wds-text); }
.wds-section.wds-compact .wds-section-body { padding: 1rem; }
.wds-section-footer { border-top: 1px solid var(--wds-border); padding: 1rem 1.5rem; }
@media (max-width: 640px) {
  .wds-section-header, .wds-section-body, .wds-section-footer { padding-inline: 1rem; }
}
</style>
@endonce
<section {{ $attributes->class(['wds-section', 'wds-section-not-contained' => ! $contained, 'wds-compact' => $compact]) }}>
  @if (($heading !== null && $heading !== '') || ($description !== null && $description !== '') || $icon)
    <header class="wds-section-header">
      @if ($icon)
        <x-webkernel::icon :name="$icon" />
      @endif
      <div class="wds-section-header-text">
        @if ($heading !== null && $heading !== '')
          <h2 class="wds-section-heading">{{ $heading }}</h2>
        @endif
        @if ($description !== null && $description !== '')
          <p class="wds-section-description">{{ $description }}</p>
        @endif
      </div>
    </header>
  @endif
  <div class="wds-section-content-ctn">
    <div class="wds-section-body">
      {!! $slot !!}
    </div>
  </div>
</section>
