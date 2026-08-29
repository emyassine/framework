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
