@props([
  'name' => '',
  'set' => 'lucide',
])
@php
  $markup = \function_exists('icon')
      ? icon((string) $name, 'wds-icon-svg', '', (string) $set)
      : '';
@endphp
<span class="wds-icon">{!! $markup !!}</span>
