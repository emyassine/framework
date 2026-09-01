@props([
  'name' => '',
  'set' => 'lucide',
])
@php
  $markup = \function_exists('icon')
      ? icon((string) $name, 'w-icon-svg', '', (string) $set)
      : '';
@endphp
<span class="w-icon">{!! $markup !!}</span>
