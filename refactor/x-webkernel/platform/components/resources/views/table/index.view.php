{{--
  <x-webkernel::table>
    <x-webkernel::table.header>
      <x-webkernel::table.header-cell>Name</x-webkernel::table.header-cell>
    </x-webkernel::table.header>
    <x-webkernel::table.body>
      <x-webkernel::table.row>
        <x-webkernel::table.cell>Notebook</x-webkernel::table.cell>
      </x-webkernel::table.row>
    </x-webkernel::table.body>
  </x-webkernel::table>
--}}
@props([
  'striped' => false,
])
<style>
.wds-ta-ctn { overflow: auto; border: 1px solid var(--wds-border); border-radius: 8px; background: var(--wds-surface); color: var(--wds-text); }
.wds-ta-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.wds-ta-table th, .wds-ta-table td { padding: 0.7em 1em; text-align: start; border-bottom: 1px solid var(--wds-border); color: var(--wds-text); }
.wds-ta-table th { font-weight: 600; color: var(--wds-text-muted); background: var(--wds-bg-subtle); }
.wds-ta-table tr:last-child td { border-bottom: 0; }
.wds-ta-row:hover td { background: var(--wds-bg-subtle); }
.wds-ta-table.wds-striped tbody tr:nth-child(even) td { background: var(--wds-bg-subtle); }
</style>
<div class="wds-ta-ctn">
  <table class="wds-ta-table{{ $striped ? ' wds-striped' : '' }}">
    {!! $slot !!}
  </table>
</div>
