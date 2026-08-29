{{--
  <x-webkernel::section heading="Branding" description="...">
    fields
  </x-webkernel::section>
--}}
@props([
  'heading' => null,
  'description' => null,
  'contained' => true,
])
<style>
.wds-section {
  background: var(--wds-surface);
  border: 1px solid var(--wds-border);
  border-radius: 8px;
  overflow: hidden;
  color: var(--wds-text);
}
.wds-section-header { padding: 1em 1.25em; border-bottom: 1px solid var(--wds-border); }
.wds-section-heading { font-size: 1rem; font-weight: 600; color: var(--wds-text); }
.wds-section-description { margin-top: 0.25em; font-size: 13px; color: var(--wds-text-muted); }
.wds-section-body { padding: 0.25em 0; color: var(--wds-text); }
</style>
<section class="wds-section">
  @if (($heading !== null && $heading !== '') || ($description !== null && $description !== ''))
    <header class="wds-section-header">
      @if ($heading !== null && $heading !== '')
        <h2 class="wds-section-heading">{{ $heading }}</h2>
      @endif
      @if ($description !== null && $description !== '')
        <p class="wds-section-description">{{ $description }}</p>
      @endif
    </header>
  @endif
  <div class="wds-section-body">
    {!! $slot !!}
  </div>
</section>
