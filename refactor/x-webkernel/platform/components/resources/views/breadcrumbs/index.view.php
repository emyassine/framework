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
@once('wds.breadcrumbs')
<style>
.wds-breadcrumbs { min-width: 0; flex: 1 1 auto; overflow: hidden; }
.wds-breadcrumbs-list {
  display: flex; flex-wrap: wrap; align-items: center;
  column-gap: 0.5rem; row-gap: 0.15rem;
  list-style: none; margin: 0; padding: 0;
}
.wds-breadcrumbs-item {
  display: flex; align-items: center; gap: 0.5rem;
  font-size: var(--wds-text-sm); font-weight: 500;
  color: var(--wds-text-muted); min-width: 0;
}
.wds-breadcrumbs-item-label {
  min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
  color: inherit; transition: color var(--wds-transition);
}
a.wds-breadcrumbs-item-label:hover { color: var(--wds-text); }
.wds-breadcrumbs-separator {
  display: inline-flex; color: var(--wds-text-faint); flex-shrink: 0;
}
.wds-breadcrumbs-separator svg { width: 0.75rem; height: 0.75rem; }
[dir="rtl"] .wds-breadcrumbs-separator-ltr { display: none; }
[dir="ltr"] .wds-breadcrumbs-separator-rtl,
:root:not([dir="rtl"]) .wds-breadcrumbs-separator-rtl { display: none; }
[dir="rtl"] .wds-breadcrumbs-separator-rtl { display: inline-flex; }
@media (max-width: 640px) {
  .wds-breadcrumbs-item:not(:last-child) { display: none; }
  .wds-breadcrumbs-item:last-child .wds-breadcrumbs-separator { display: none; }
}
</style>
@endonce
@if ($items !== [])
<nav {{ $attributes->class('wds-breadcrumbs')->merge(['aria-label' => $label]) }}>
  <ol class="wds-breadcrumbs-list">
    @foreach ($items as $i => $crumb)
      <li class="wds-breadcrumbs-item">
        @if ($i > 0)
          <span class="wds-breadcrumbs-separator wds-breadcrumbs-separator-ltr" aria-hidden="true">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 4l4 4-4 4"/></svg>
          </span>
          <span class="wds-breadcrumbs-separator wds-breadcrumbs-separator-rtl" aria-hidden="true">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 4L6 8l4 4"/></svg>
          </span>
        @endif
        @php
          $is_last = $i === \count($items) - 1;
          $current_attr = $is_last ? ' aria-current="page"' : '';
        @endphp
        @if ($crumb['href'] !== '')
          <a class="wds-breadcrumbs-item-label" href="{{ $crumb['href'] }}"{!! $current_attr !!}>{{ $crumb['label'] }}</a>
        @else
          <span class="wds-breadcrumbs-item-label"{!! $current_attr !!}>{{ $crumb['label'] }}</span>
        @endif
      </li>
    @endforeach
  </ol>
</nav>
@endif
