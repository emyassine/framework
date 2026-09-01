@props([
  'id' => '',
  'title' => '',
  'body' => null,
  'status' => 'info',
  'icon' => null,
  'duration' => 6000,
])
@php
  $status = (string) $status;
  $icon = $icon ?: match ($status) {
    'success' => 'check-circle',
    'danger' => 'x-circle',
    'warning' => 'alert-circle',
    default => 'info',
  };
  $role = $status === 'danger' ? 'alert' : null;
@endphp
<div
  class="w-no-notification w-status-{{ $status }}"
  data-w-notification
  data-duration="{{ $duration }}"
  @if ($id) data-notification-id="{{ $id }}" @endif
  @if ($role) role="{{ $role }}" @endif
>
  <span class="w-no-notification-icon w-color-{{ $status }}">
    <x-webkernel::icon :name="$icon" />
  </span>
  <div class="w-no-notification-main">
    <div class="w-no-notification-text">
      @if ($title !== '')
        <h3 class="w-no-notification-title">{{ $title }}</h3>
      @endif
      @if ($body)
        <div class="w-no-notification-body">{{ $body }}</div>
      @endif
    </div>
  </div>
  <button type="button" class="w-icon-btn w-no-notification-close-btn" data-w-notification-close aria-label="Close" title="Close">
    <x-webkernel::icon name="x" />
  </button>
</div>
