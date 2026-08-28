@extends('webkernel::layouts.simple')

@section('content')
  <h1>{{ $title ?? 'System' }}</h1>
  <p>System Admin Panel.</p>
  <p>
    <a href="/">Home</a>
    ·
    <a href="/billing/invoices">Invoices</a>
  </p>
@endsection
