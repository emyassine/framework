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
<nav {{ $attributes->class(['w-pagination', 'w-simple' => $is_simple])->merge(['aria-label' => lang('panel.pagination')]) }}>
  @if ($current > 1)
    <x-webkernel::button color="gray" size="sm" :href="$prev_href" class="w-pagination-previous">{{ lang('panel.pagination_previous') }}</x-webkernel::button>
  @endif
  @if ($from !== null && $to !== null && $total !== null)
    <span class="w-pagination-overview">{{ $from }}–{{ $to }} / {{ $total }}</span>
  @endif
  @if ($pages !== [])
    <ol class="w-pagination-items">
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
    <x-webkernel::button color="gray" size="sm" :href="$next_href" class="w-pagination-next">{{ lang('panel.pagination_next') }}</x-webkernel::button>
  @endif
</nav>
