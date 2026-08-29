@extends('webkernel::layouts.page')

@section('title', 'Invoices')

@section('breadcrumb')
  Billing / Invoices
@endsection

@section('content')
  <x-webkernel::page title="Invoices">
    @slot('actions')
      <a href="/billing/invoices/create">Create invoice</a>
    @endslot

    <table>
      <thead>
        <tr>
          @foreach ($columns as $column)
            <th>{{ $column['label'] }}</th>
          @endforeach
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($invoices as $invoice)
          <tr>
            @foreach ($columns as $column)
              <td>{{ $invoice->{$column['key']} }}</td>
            @endforeach
            <td><a href="/billing/invoices/{{ $invoice->id }}/edit">Edit</a></td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ count($columns) + 1 }}">No invoices yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-webkernel::page>
@endsection
