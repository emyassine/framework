{{--
  <x-webkernel::callout heading="Heads up" color="warning" icon="triangle-alert">Body</x-webkernel::callout>
--}}
@props([
  'color' => 'gray',
  'heading' => null,
  'description' => null,
  'icon' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $has_heading = $heading !== null && $heading !== '';
  $has_description = $description !== null && $description !== '';
  $has_icon = $icon !== null && $icon !== '';
  $slot = $slot ?? '';
@endphp
@once('wds.callout')
<style>
.wds-callout {
  display: flex; width: 100%; gap: 0.75rem;
  border-radius: var(--wds-radius-lg);
  padding: 1rem;
  background: var(--wds-surface);
  box-shadow: 0 0 0 1px color-mix(in srgb, var(--wds-text) 5%, transparent);
  color: var(--wds-text);
}
.wds-callout-icon { color: var(--wds-text-faint); flex-shrink: 0; }
.wds-callout-icon .wds-icon { font-size: 1.25rem; }
.wds-callout-main { margin-top: 0.125rem; display: grid; flex: 1; gap: 0.75rem; min-width: 0; }
.wds-callout-text { display: grid; gap: 0.25rem; }
.wds-callout-heading { font-size: var(--wds-text-sm); font-weight: 500; color: var(--wds-text); }
.wds-callout-description { overflow: hidden; font-size: var(--wds-text-sm); color: var(--wds-text-muted); overflow-wrap: anywhere; }
.wds-callout-footer { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.wds-callout.wds-color-primary { background: color-mix(in oklab, var(--wds-surface) 90%, var(--primary-400)); box-shadow: 0 0 0 1px color-mix(in srgb, var(--primary-600) 20%, transparent); }
.wds-callout.wds-color-primary .wds-callout-icon { color: var(--primary-500); }
.wds-callout.wds-color-warning { background: color-mix(in oklab, var(--wds-surface) 90%, var(--warning-400)); box-shadow: 0 0 0 1px color-mix(in srgb, var(--warning-600) 20%, transparent); }
.wds-callout.wds-color-warning .wds-callout-icon { color: var(--warning-500); }
.wds-callout.wds-color-danger { background: color-mix(in oklab, var(--wds-surface) 90%, var(--danger-400)); box-shadow: 0 0 0 1px color-mix(in srgb, var(--danger-600) 20%, transparent); }
.wds-callout.wds-color-danger .wds-callout-icon { color: var(--danger-500); }
.wds-callout.wds-color-success { background: color-mix(in oklab, var(--wds-surface) 90%, var(--success-400)); box-shadow: 0 0 0 1px color-mix(in srgb, var(--success-600) 20%, transparent); }
.wds-callout.wds-color-success .wds-callout-icon { color: var(--success-500); }
.wds-callout.wds-color-info { background: color-mix(in oklab, var(--wds-surface) 90%, var(--info-400)); box-shadow: 0 0 0 1px color-mix(in srgb, var(--info-600) 20%, transparent); }
.wds-callout.wds-color-info .wds-callout-icon { color: var(--info-500); }
</style>
@endonce
<div {{ $attributes->class(['wds-callout', 'wds-color-'.$color => $color !== 'gray']) }}>
  @if ($has_icon)
    <span class="wds-callout-icon" aria-hidden="true"><x-webkernel::icon :name="$icon" /></span>
  @endif
  @if ($has_heading || $has_description || $slot !== '')
    <div class="wds-callout-main">
      @if ($has_heading || $has_description)
        <div class="wds-callout-text">
          @if ($has_heading)
            <h4 class="wds-callout-heading">{{ $heading }}</h4>
          @endif
          @if ($has_description)
            <p class="wds-callout-description">{{ $description }}</p>
          @endif
        </div>
      @endif
      @if ($slot !== '')
        <div class="wds-callout-footer">{!! $slot !!}</div>
      @endif
    </div>
  @endif
</div>
