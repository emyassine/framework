@extends('webkernel::layouts.page')

@section('title', 'Home')

@section('navigation')
  @include('webkernel::panels.system.sidebar', ['current' => 'home'])
@endsection

@section('user')
  @include('webkernel::panels.system.user')
@endsection

@section('topnav')
  @include('webkernel::panels.system.sidebar', ['current' => 'home'])
@endsection

@section('horizontal')
  @include('webkernel::panels.system.sidebar', ['current' => 'home'])
@endsection

@section('breadcrumb')
  Home
@endsection

@section('content')
  <x-webkernel::page title="Webkernel">
    <p>Platform is working.</p>
    <p>Open the <a href="/system">system panel</a> or <a href="/billing/invoices">invoices</a>.</p>
  </x-webkernel::page>
@endsection
