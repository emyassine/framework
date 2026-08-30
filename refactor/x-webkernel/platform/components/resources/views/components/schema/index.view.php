{{--
  Schema form shell. Page views only echo the schema.
--}}
@props([
  'action' => '',
  'autosave' => false,
  'header' => '',
  'footer' => '',
  'hidden' => [],
  'grid_class' => '',
  'grid_style' => '',
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $action = \is_string($action) ? $action : '';
  $hidden = \is_array($hidden) ? $hidden : [];
@endphp
<form
  {{ $attributes->class(['w-schema'])->merge([
    'id' => 'w-schema',
    'method' => 'post',
    'action' => $action,
    'hx-post' => $action,
    'hx-target' => '#w-schema',
    'hx-swap' => 'outerHTML',
  ] + ($autosave ? [
    'hx-trigger' => 'submit, change delay:400ms from:input, change delay:400ms from:textarea, change delay:400ms from:select',
  ] : [])) }}
>
  {!! \Webkernel\Csrf::field() !!}
  @foreach ($hidden as $name => $value)
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
  @endforeach
  @if ($header !== '')
    <div class="w-schema-header">{!! $header !!}</div>
  @endif
  <div class="{{ $grid_class !== '' ? $grid_class : '' }}"@if ($grid_style !== '') style="{{ $grid_style }}"@endif>
    {!! $slot !!}
  </div>
  @if ($footer !== '')
    <div class="w-form-actions">{!! $footer !!}</div>
  @endif
</form>
