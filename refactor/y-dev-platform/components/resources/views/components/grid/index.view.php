{{--
  <x-webkernel::grid>…</x-webkernel::grid>
--}}
@props([
  'dense' => false,
  'gap' => true,
  'grid_container' => false,
  'grid_class' => '',
  'grid_style' => '',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $class = \trim(($grid_class !== '' ? $grid_class : 'w-grid').($dense && ! \str_contains((string) $grid_class, 'w-dense') ? ' w-dense' : '').(! $gap && ! \str_contains((string) $grid_class, 'w-no-gap') ? ' w-no-gap' : '').($grid_container && ! \str_contains((string) $grid_class, 'w-grid-container') ? ' w-grid-container' : ''));
  $style = \trim((string) $attributes->get('style').' '.$grid_style);
@endphp
<div {{ $attributes->class([$class])->merge($style !== '' ? ['style' => $style] : []) }}>
  {!! $slot !!}
</div>
