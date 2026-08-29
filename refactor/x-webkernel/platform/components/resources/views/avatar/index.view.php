@props([
  'alt' => '',
  'src' => null,
  'circular' => true,
  'size' => 'md',
])
@php
  $size = \in_array((string) $size, ['sm', 'md', 'lg'], true) ? (string) $size : 'md';
@endphp
<style>
.wds-avatar {
  width: 36px; height: 36px;
  border-radius: var(--wds-radius-full);
  background: var(--primary-200);
  color: var(--primary-800);
  font-size: 0.75rem;
  font-weight: 700;
  display: grid;
  place-content: center;
  flex-shrink: 0;
  object-fit: cover;
}
.wds-avatar.wds-circular { border-radius: 9999px; }
.wds-avatar.wds-size-sm { width: 24px; height: 24px; font-size: 0.65rem; }
.wds-avatar.wds-size-lg { width: 48px; height: 48px; }
</style>
@if (!empty($src))
  <img
    class="wds-avatar{{ $circular ? ' wds-circular' : '' }} wds-size-{{ $size }}"
    src="{{ $src }}"
    alt="{{ $alt }}"
  />
@else
  <span class="wds-avatar{{ $circular ? ' wds-circular' : '' }} wds-size-{{ $size }}">{{ strtoupper(substr((string) ($alt !== '' ? $alt : 'W'), 0, 1)) }}</span>
@endif
