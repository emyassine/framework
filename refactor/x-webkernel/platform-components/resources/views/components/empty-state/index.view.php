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
<div {{ $attributes->class([
  'w-empty-state',
  'w-compact' => $compact,
  'w-empty-state-not-contained' => ! $contained,
]) }}>
  <div class="w-empty-state-content">
    @if ($has_icon)
      <div class="w-empty-state-icon w-color-{{ $icon_color }}">
        <x-webkernel::icon :name="$icon" />
      </div>
    @endif
    <div class="w-empty-state-text">
      <{{ $heading_tag }} class="w-empty-state-heading">{{ $heading }}</{{ $heading_tag }}>
      @if ($has_description)
        <p class="w-empty-state-description">{{ $description }}</p>
      @endif
      @if ($slot !== '')
        <footer class="w-empty-state-footer">{!! $slot !!}</footer>
      @endif
    </div>
  </div>
</div>
