@extends('webkernel::layouts.simple')

@section('content')
  <h1>{{ $title }}</h1>
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
@endsection
