@props([
  'alt' => '',
  'src' => null,
  'circular' => true,
  'size' => 'md',
])
@php
  $size = \in_array((string) $size, ['sm', 'md', 'lg'], true) ? (string) $size : 'md';
@endphp
@if (!empty($src))
  <img
    class="w-avatar{{ $circular ? ' w-circular' : '' }} w-size-{{ $size }}"
    src="{{ $src }}"
    alt="{{ $alt }}"
  />
@else
  <span class="w-avatar{{ $circular ? ' w-circular' : '' }} w-size-{{ $size }}">{{ strtoupper(substr((string) ($alt !== '' ? $alt : 'W'), 0, 1)) }}</span>
@endif
