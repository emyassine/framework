@props([
  'tab' => '',
  'active' => false,
  'columns' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $cols = \is_int($columns) && $columns > 1 ? $columns : null;
@endphp
<section
  {{ $attributes->class(['w-sc-tabs-tab', 'w-active' => $active])->merge([
    'role' => 'tabpanel',
    'data-tab-panel' => $tab,
    'aria-labelledby' => 'tab-'.$tab,
  ]) }}
  @if ($cols) style="--w-cols: {{ $cols }}" @endif
>
  {!! $slot !!}
</section>
