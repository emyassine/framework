@props(['align' => 'start'])
@once('wds.table.header-cell')
<style>
th.wds-align-start { text-align: start; }
th.wds-align-center { text-align: center; }
th.wds-align-end { text-align: end; }
</style>
@endonce
<th class="wds-align-{{ $align }}">{!! $slot !!}</th>
