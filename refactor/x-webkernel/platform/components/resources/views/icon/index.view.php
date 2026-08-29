@props([
  'name' => '',
  'set' => 'lucide',
])
@php
  $markup = \function_exists('icon')
      ? icon((string) $name, 'webkernel-shell-icon__svg', '', (string) $set)
      : '';
@endphp
<span class="webkernel-shell-icon">{!! $markup !!}</span>
