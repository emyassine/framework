@extends('webkernel::layouts.simple')

@section('content')
  <h1>{{ $title ?? 'Webkernel' }}</h1>
  <p>Platform is working.</p>
  <p>
    <a href="/system">System panel</a>
    ·
    <a href="/billing/invoices">Invoices</a>
  </p>
@endsection
