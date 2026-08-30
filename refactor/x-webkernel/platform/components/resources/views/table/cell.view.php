@props(['align' => 'start'])
@once('wds.table.cell')
<style>
.wds-align-start { text-align: start; }
.wds-align-center { text-align: center; }
.wds-align-end { text-align: end; }
</style>
@endonce
<td class="wds-align-{{ $align }}">{!! $slot !!}</td>
