@extends('webkernel::layouts.page')

@section('title'){{ $title }}@endsection

@section('breadcrumb')
  Billing / Invoices / {{ $title }}
@endsection

@section('content')
  <x-webkernel::page :title="$title">
    <form method="post" action="{{ $action }}">
      {!! $schema->render($state) !!}
      <p>
        <x-webkernel::button type="submit" color="primary">Save</x-webkernel::button>
        <x-webkernel::button href="/billing/invoices" tag="a" color="gray">Cancel</x-webkernel::button>
      </p>
    </form>
  </x-webkernel::page>
@endsection
