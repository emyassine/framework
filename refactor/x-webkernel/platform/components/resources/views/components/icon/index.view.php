@props([
  'name' => '',
  'set' => 'lucide',
])
@php
  $markup = \function_exists('icon')
      ? icon((string) $name, 'wds-icon-svg', '', (string) $set)
      : '';
@endphp
@once('wds.icon')
<style>
.wds-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: inherit;
  line-height: 0;
}
.wds-icon svg,
.wds-icon .wds-icon-svg,
svg.wds-icon-svg {
  width: 1em;
  height: 1em;
  display: block;
  stroke: currentColor;
  fill: none;
  stroke-width: 1.75;
  stroke-linecap: round;
  stroke-linejoin: round;
}
</style>
@endonce
<span class="wds-icon">{!! $markup !!}</span>
