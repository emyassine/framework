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
<div {{ $attributes->class(['w-callout', 'w-color-'.$color => $color !== 'gray']) }}>
  @if ($has_icon)
    <span class="w-callout-icon" aria-hidden="true"><x-webkernel::icon :name="$icon" /></span>
  @endif
  @if ($has_heading || $has_description || $slot !== '')
    <div class="w-callout-main">
      @if ($has_heading || $has_description)
        <div class="w-callout-text">
          @if ($has_heading)
            <h4 class="w-callout-heading">{{ $heading }}</h4>
          @endif
          @if ($has_description)
            <p class="w-callout-description">{{ $description }}</p>
          @endif
        </div>
      @endif
      @if ($slot !== '')
        <div class="w-callout-footer">{!! $slot !!}</div>
      @endif
    </div>
  @endif
</div>
