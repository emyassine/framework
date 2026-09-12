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
<div class="w-ta-ctn">
  <table class="w-ta-table{{ $striped ? ' w-striped' : '' }}">
    {!! $slot !!}
  </table>
</div>
