{{--
  <x-webkernel::pagination :current="1" :last="8" :total="73" />
--}}
@props([
  'current' => 1,
  'last' => 1,
  'total' => null,
  'from' => null,
  'to' => null,
  'simple' => false,
  'prev_href' => null,
  'next_href' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
  $current = \max(1, (int) $current);
  $last = \max(1, (int) $last);
  $is_simple = (bool) $simple;
  $pages = [];
  if (! $is_simple && $last > 1) {
    $start = \max(1, $current - 2);
    $end = \min($last, $current + 2);
    if ($start > 1) {
      $pages[] = 1;
      if ($start > 2) {
        $pages[] = null;
      }
    }
    for ($i = $start; $i <= $end; $i++) {
      $pages[] = $i;
    }
    if ($end < $last) {
      if ($end < $last - 1) {
        $pages[] = null;
      }
      $pages[] = $last;
    }
  }
  $from = $from !== null ? (int) $from : null;
  $to = $to !== null ? (int) $to : null;
  $total = $total !== null ? (int) $total : null;
@endphp
@once('wds.pagination')
<style>
.wds-pagination {
  display: grid; grid-template-columns: 1fr auto 1fr;
  align-items: center; column-gap: 0.75rem;
  color: var(--wds-text);
}
.wds-pagination:empty { display: none; }
.wds-pagination-previous { justify-self: start; }
.wds-pagination-next { grid-column-start: 3; justify-self: end; }
.wds-pagination-overview {
  display: none; font-size: var(--wds-text-sm); font-weight: 500; color: var(--wds-text);
}
.wds-pagination-items {
  display: none; justify-self: end;
  border-radius: var(--wds-radius-lg);
  background: var(--wds-surface);
  box-shadow: 0 1px 2px color-mix(in srgb, var(--wds-text) 6%, transparent), 0 0 0 1px color-mix(in srgb, var(--wds-text) 10%, transparent);
  list-style: none; margin: 0; padding: 0;
}
.wds-pagination-item {
  border-inline: 0.5px solid var(--wds-border);
}
.wds-pagination-item:first-child { border-inline-start: 0; }
.wds-pagination-item:last-child { border-inline-end: 0; }
.wds-pagination-item-btn {
  position: relative; display: flex; overflow: hidden;
  padding: 0.5rem; outline: none; color: var(--wds-text);
}
.wds-pagination-item-btn:hover { background: var(--wds-bg-subtle); }
.wds-pagination-item.wds-active .wds-pagination-item-btn { background: var(--wds-bg-subtle); }
.wds-pagination-item.wds-active .wds-pagination-item-label { color: var(--primary-700); }
.wds-pagination-item:first-child .wds-pagination-item-btn { border-radius: var(--wds-radius-lg) 0 0 var(--wds-radius-lg); }
.wds-pagination-item:last-child .wds-pagination-item-btn { border-radius: 0 var(--wds-radius-lg) var(--wds-radius-lg) 0; }
.wds-pagination-item-label { padding-inline: 0.375rem; font-size: var(--wds-text-sm); font-weight: 600; }
.wds-pagination-item.wds-disabled .wds-pagination-item-label { color: var(--wds-text-muted); }
@media (min-width: 768px) {
  .wds-pagination:not(.wds-simple) .wds-pagination-previous,
  .wds-pagination:not(.wds-simple) .wds-pagination-next { display: none; }
  .wds-pagination-overview { display: inline; }
  .wds-pagination-items { display: flex; }
}
@media (max-width: 767px) {
  .wds-pagination { gap: 0.5rem; }
}
</style>
@endonce
<nav {{ $attributes->class(['wds-pagination', 'wds-simple' => $is_simple])->merge(['aria-label' => lang('panel.pagination')]) }}>
  @if ($current > 1)
    <x-webkernel::button color="gray" size="sm" :href="$prev_href" class="wds-pagination-previous">{{ lang('panel.pagination_previous') }}</x-webkernel::button>
  @endif
  @if ($from !== null && $to !== null && $total !== null)
    <span class="wds-pagination-overview">{{ $from }}–{{ $to }} / {{ $total }}</span>
  @endif
  @if ($pages !== [])
    <ol class="wds-pagination-items">
      @foreach ($pages as $page)
        @if ($page === null)
          <x-webkernel::pagination.item disabled label="…" />
        @else
          <x-webkernel::pagination.item :active="$page === $current" :label="(string) $page" />
        @endif
      @endforeach
    </ol>
  @endif
  @if ($current < $last)
    <x-webkernel::button color="gray" size="sm" :href="$next_href" class="wds-pagination-next">{{ lang('panel.pagination_next') }}</x-webkernel::button>
  @endif
</nav>
