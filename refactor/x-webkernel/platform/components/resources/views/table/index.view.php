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
<div class="wds-ta-ctn">
  <table class="wds-ta-table{{ $striped ? ' wds-striped' : '' }}">
    {!! $slot !!}
  </table>
</div>
