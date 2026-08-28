@extends('webkernel::layouts.page')

@section('title'){{ $title }}@endsection

@section('navigation')
  @include('billing::sidebar', ['current' => 'invoices'])
@endsection

@section('user')
  @include('webkernel::panels.system.user')
@endsection

@section('topnav')
  @include('billing::sidebar', ['current' => 'invoices'])
@endsection

@section('horizontal')
  @include('billing::sidebar', ['current' => 'invoices'])
@endsection

@section('breadcrumb')
  Billing / Invoices / {{ $title }}
@endsection

@section('content')
  <x-webkernel::page :title="$title">
    <form method="post" action="{{ $action }}">
      @foreach ($fields as $field)
        <p>
          <label>
            {{ $field['label'] }}
            <input
              type="{{ $field['type'] }}"
              name="{{ $field['name'] }}"
              value="{{ $invoice?->{$field['name']} ?? '' }}"
            />
          </label>
        </p>
      @endforeach
      <p>
        <button type="submit">Save</button>
        <a href="/billing/invoices">Cancel</a>
      </p>
    </form>
  </x-webkernel::page>
@endsection
