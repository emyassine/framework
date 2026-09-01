{{--
  <x-webkernel::breadcrumbs :breadcrumbs="[['label' => 'System', 'href' => '/system'], ['label' => 'Overview']]" />
--}}
@props([
  'breadcrumbs' => [],
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $items = [];
  if (\is_array($breadcrumbs)) {
    $is_list = \array_is_list($breadcrumbs);
    foreach ($breadcrumbs as $key => $value) {
      if (\is_array($value)) {
        $items[] = [
          'label' => (string) ($value['label'] ?? ''),
          'href' => (string) ($value['href'] ?? ''),
        ];
        continue;
      }
      if ($is_list || \is_int($key)) {
        $items[] = ['label' => (string) $value, 'href' => ''];
        continue;
      }
      $items[] = ['label' => (string) $value, 'href' => (string) $key];
    }
  }
  $label = \function_exists('lang') ? lang('panel.breadcrumbs') : 'Breadcrumb';
@endphp
@if ($items !== [])
<nav {{ $attributes->class('w-breadcrumbs')->merge(['aria-label' => $label]) }}>
  <ol class="w-breadcrumbs-list">
    @foreach ($items as $i => $crumb)
      <li class="w-breadcrumbs-item">
        @if ($i > 0)
          <span class="w-breadcrumbs-separator w-breadcrumbs-separator-ltr" aria-hidden="true">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 4l4 4-4 4"/></svg>
          </span>
          <span class="w-breadcrumbs-separator w-breadcrumbs-separator-rtl" aria-hidden="true">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 4L6 8l4 4"/></svg>
          </span>
        @endif
        @php
          $is_last = $i === \count($items) - 1;
          $current_attr = $is_last ? ' aria-current="page"' : '';
        @endphp
        @if ($crumb['href'] !== '')
          <a class="w-breadcrumbs-item-label" href="{{ $crumb['href'] }}"{!! $current_attr !!}>{{ $crumb['label'] }}</a>
        @else
          <span class="w-breadcrumbs-item-label"{!! $current_attr !!}>{{ $crumb['label'] }}</span>
        @endif
      </li>
    @endforeach
  </ol>
</nav>
@endif
