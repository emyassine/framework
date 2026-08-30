@props([
  'href' => null,
])
@once('wds.table.row')
<style>
.wds-ta-row:hover td { background: var(--wds-bg-subtle); }
</style>
@endonce
<tr class="wds-ta-row" @if (!empty($href)) onclick="window.location.href={{ json_encode($href) }}" style="cursor:pointer" @endif>
  {!! $slot !!}
</tr>
