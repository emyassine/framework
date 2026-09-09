{{--
  <x-webkernel::input.wrapper prefix_icon="search">
    <x-webkernel::input name="q" />
  </x-webkernel::input.wrapper>
--}}
@props([
  'disabled' => false,
  'valid' => true,
  'prefix' => null,
  'prefix_icon' => null,
  'suffix' => null,
  'suffix_icon' => null,
  'inline_prefix' => false,
  'inline_suffix' => false,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $has_prefix = ($prefix !== null && $prefix !== '') || ($prefix_icon !== null && $prefix_icon !== '');
  $has_suffix = ($suffix !== null && $suffix !== '') || ($suffix_icon !== null && $suffix_icon !== '');
@endphp
<div {{ $attributes->class([
  'w-input-wrp',
  'w-disabled' => $disabled,
  'w-invalid' => ! $valid,
]) }}>
  @if ($has_prefix)
    <div class="w-input-wrp-prefix{{ $inline_prefix ? ' w-inline' : '' }}">
      @if ($prefix_icon)
        <x-webkernel::icon :name="$prefix_icon" />
      @endif
      @if ($prefix !== null && $prefix !== '')
        <span class="w-input-wrp-label">{{ $prefix }}</span>
      @endif
    </div>
  @endif
  <div class="w-input-wrp-content">{!! $slot !!}</div>
  @if ($has_suffix)
    <div class="w-input-wrp-suffix{{ $inline_suffix ? ' w-inline' : '' }}">
      @if ($suffix !== null && $suffix !== '')
        <span class="w-input-wrp-label">{{ $suffix }}</span>
      @endif
      @if ($suffix_icon)
        <x-webkernel::icon :name="$suffix_icon" />
      @endif
    </div>
  @endif
</div>
