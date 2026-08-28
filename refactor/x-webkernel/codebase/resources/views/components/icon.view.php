@php
  $markup = function_exists('webkernel_grab_icon')
      ? webkernel_grab_icon((string) ($name ?? ''), 'webkernel-shell-icon__svg', '', (string) ($set ?? 'lucide'))
      : '';
@endphp
<span class="webkernel-shell-icon">{!! $markup !!}</span>
