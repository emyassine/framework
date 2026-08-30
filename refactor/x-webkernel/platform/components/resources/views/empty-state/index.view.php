{{--
  <x-webkernel::empty-state heading="No invoices" description="Create one to get started." icon="receipt" />
--}}
@props([
  'heading' => '',
  'description' => null,
  'icon' => null,
  'icon_color' => 'primary',
  'compact' => false,
  'contained' => true,
  'heading_tag' => 'h2',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $has_description = $description !== null && $description !== '';
  $has_icon = $icon !== null && $icon !== '';
  $slot = $slot ?? '';
@endphp
@once('wds.empty-state')
<style>
.wds-empty-state { padding: 3rem 1.5rem; color: var(--wds-text); }
.wds-empty-state:not(.wds-empty-state-not-contained) {
  border-radius: var(--wds-radius-lg);
  background: var(--wds-surface);
  box-shadow: 0 1px 2px color-mix(in srgb, var(--wds-text) 6%, transparent), 0 0 0 1px color-mix(in srgb, var(--wds-text) 5%, transparent);
}
.wds-empty-state-content {
  margin-inline: auto; display: grid; max-width: 32rem;
  justify-items: center; text-align: center;
}
.wds-empty-state-text { display: grid; justify-items: center; text-align: center; }
.wds-empty-state-icon {
  margin-bottom: 1rem; border-radius: 9999px;
  background: var(--wds-bg-subtle); padding: 0.75rem;
}
.wds-empty-state-icon .wds-icon { font-size: 1.5rem; color: var(--wds-text-muted); }
.wds-empty-state-icon.wds-color-primary { background: var(--primary-100); }
.wds-empty-state-icon.wds-color-primary .wds-icon { color: var(--primary-600); }
.wds-empty-state-heading { font-size: var(--wds-text-md); line-height: 1.5; font-weight: 600; color: var(--wds-text); }
.wds-empty-state-description { margin-top: 0.25rem; font-size: var(--wds-text-sm); color: var(--wds-text-muted); }
.wds-empty-state-footer { margin-top: 1.5rem; }
.wds-empty-state.wds-compact { padding-block: 1.5rem; }
.wds-empty-state.wds-compact .wds-empty-state-content {
  margin: 0; display: flex; max-width: none; align-items: flex-start;
  gap: 1rem; text-align: start;
}
.wds-empty-state.wds-compact .wds-empty-state-icon { margin-bottom: 0; flex-shrink: 0; }
.wds-empty-state.wds-compact .wds-empty-state-text { flex: 1; justify-items: start; text-align: start; }
.wds-empty-state.wds-compact .wds-empty-state-footer { margin-top: 1rem; }
@media (max-width: 640px) {
  .wds-empty-state { padding: 2rem 1rem; }
}
</style>
@endonce
<div {{ $attributes->class([
  'wds-empty-state',
  'wds-compact' => $compact,
  'wds-empty-state-not-contained' => ! $contained,
]) }}>
  <div class="wds-empty-state-content">
    @if ($has_icon)
      <div class="wds-empty-state-icon wds-color-{{ $icon_color }}">
        <x-webkernel::icon :name="$icon" />
      </div>
    @endif
    <div class="wds-empty-state-text">
      <{{ $heading_tag }} class="wds-empty-state-heading">{{ $heading }}</{{ $heading_tag }}>
      @if ($has_description)
        <p class="wds-empty-state-description">{{ $description }}</p>
      @endif
      @if ($slot !== '')
        <footer class="wds-empty-state-footer">{!! $slot !!}</footer>
      @endif
    </div>
  </div>
</div>
