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
    class="wds-avatar{{ $circular ? ' wds-circular' : '' }} wds-size-{{ $size }}"
    src="{{ $src }}"
    alt="{{ $alt }}"
  />
@else
  <span class="wds-avatar{{ $circular ? ' wds-circular' : '' }} wds-size-{{ $size }}">{{ strtoupper(substr((string) ($alt !== '' ? $alt : 'W'), 0, 1)) }}</span>
@endif
