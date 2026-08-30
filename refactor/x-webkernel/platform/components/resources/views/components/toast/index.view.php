@props([
  'title' => '',
  'status' => 'info',
])
<div class="w-toast w-toast-{{ $status }}" role="status">{{ $title }}</div>
