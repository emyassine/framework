{{--
  <x-webkernel::modal heading="Delete" id="delete">
    <x-slot name="trigger"><x-webkernel::button color="danger">Delete</x-webkernel::button></x-slot>
    Confirm.
    <x-slot name="footer"><x-webkernel::button>Close</x-webkernel::button></x-slot>
  </x-webkernel::modal>
--}}
@props([
  'id' => null,
  'heading' => null,
  'description' => null,
  'icon' => null,
  'icon_color' => 'primary',
  'width' => 'sm',
  'slide_over' => false,
  'close_button' => true,
  'open' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $id = $id !== null && $id !== '' ? (string) $id : 'w-modal';
  $has_heading = $heading !== null && $heading !== '';
  $has_description = $description !== null && $description !== '';
  $has_icon = $icon !== null && $icon !== '';
  $has_trigger = isset($trigger) && $trigger !== '';
  $has_footer = isset($footer) && $footer !== '';
  $open_attr = $open ? 'true' : 'false';
@endphp
<div
  {{ $attributes->class(['w-modal', 'w-slide-over' => $slide_over, 'w-open' => $open]) }}
  data-w-modal
  data-modal-id="{{ $id }}"
  data-open="{{ $open_attr }}"
>
  @if ($has_trigger)
    <div class="w-modal-trigger" data-w-modal-trigger>
      {!! $trigger !!}
    </div>
  @endif
  <div class="w-modal-close-overlay" data-w-modal-overlay></div>
  <div class="w-modal-window-ctn">
    <div class="w-modal-window w-width-{{ $width }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-heading">
      @if ($close_button)
        <div class="w-modal-close-btn">
          <x-webkernel::button-icon icon="x" :label="lang('panel.close')" size="sm" color="gray" data-w-modal-close />
        </div>
      @endif
      @if ($has_heading || $has_icon)
        <div class="w-modal-header">
          @if ($has_icon)
            <span class="w-modal-icon w-color-{{ $icon_color }}" aria-hidden="true"><x-webkernel::icon :name="$icon" /></span>
          @endif
          <div>
            @if ($has_heading)
              <h2 class="w-modal-heading" id="{{ $id }}-heading">{{ $heading }}</h2>
            @endif
            @if ($has_description)
              <p class="w-modal-description">{{ $description }}</p>
            @endif
          </div>
        </div>
      @endif
      <div class="w-modal-content">{!! $slot !!}</div>
      @if ($has_footer)
        <div class="w-modal-footer">
          <div class="w-modal-footer-actions">{!! $footer !!}</div>
        </div>
      @endif
    </div>
  </div>
</div>
