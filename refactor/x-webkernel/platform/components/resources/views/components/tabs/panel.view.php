@props([
  'tab' => '',
  'active' => false,
  'columns' => null,
  'grid_class' => '',
  'grid_style' => '',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $grid = \is_string($grid_class) ? \trim($grid_class) : '';
  $style = \trim((string) $attributes->get('style').' '.$grid_style);
@endphp
<section
  {{ $attributes->class(['w-sc-tabs-tab', 'w-active' => $active, $grid => $grid !== ''])->merge([
    'role' => 'tabpanel',
    'data-tab-panel' => $tab,
    'aria-labelledby' => 'tab-'.$tab,
  ] + ($style !== '' ? ['style' => $style] : [])) }}
>
  {!! $slot !!}
</section>
