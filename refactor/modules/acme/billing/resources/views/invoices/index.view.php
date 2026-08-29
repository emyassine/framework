@if ($invoices === [])
  <p>No invoices yet.</p>
@else
  <x-webkernel::table>
    <x-webkernel::table.header>
      @foreach ($columns as $column)
        <x-webkernel::table.header-cell>{{ $column['label'] }}</x-webkernel::table.header-cell>
      @endforeach
      <x-webkernel::table.header-cell></x-webkernel::table.header-cell>
    </x-webkernel::table.header>
    <x-webkernel::table.body>
      @foreach ($invoices as $invoice)
        <x-webkernel::table.row :href="'/billing/invoices/'.$invoice->id.'/edit'">
          @foreach ($columns as $column)
            <x-webkernel::table.cell>{{ $invoice->{$column['key']} }}</x-webkernel::table.cell>
          @endforeach
          <x-webkernel::table.cell>
            <x-webkernel::button href="/billing/invoices/{{ $invoice->id }}/edit" tag="a" size="sm" color="gray">Edit</x-webkernel::button>
          </x-webkernel::table.cell>
        </x-webkernel::table.row>
      @endforeach
    </x-webkernel::table.body>
  </x-webkernel::table>
@endif
