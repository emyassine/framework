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
<section {{ $attributes->class(['w-section', 'w-section-not-contained' => ! $contained, 'w-compact' => $compact]) }}>
  @if (($heading !== null && $heading !== '') || ($description !== null && $description !== '') || $icon)
    <header class="w-section-header">
      @if ($icon)
        <x-webkernel::icon :name="$icon" />
      @endif
      <div class="w-section-header-text">
        @if ($heading !== null && $heading !== '')
          <h2 class="w-section-heading">{{ $heading }}</h2>
        @endif
        @if ($description !== null && $description !== '')
          <p class="w-section-description">{{ $description }}</p>
        @endif
      </div>
    </header>
  @endif
  <div class="w-section-content-ctn">
    <div class="w-section-body">
      {!! $slot !!}
    </div>
  </div>
</section>
