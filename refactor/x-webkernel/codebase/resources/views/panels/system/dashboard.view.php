@extends('webkernel::layouts.page')

@section('navigation')
  @include('webkernel::panels.system.sidebar', ['current' => 'dashboard'])
@endsection

@section('user')
  @include('webkernel::panels.system.user')
@endsection

@section('topnav')
  @include('webkernel::panels.system.sidebar', ['current' => 'dashboard'])
@endsection

@section('horizontal')
  @include('webkernel::panels.system.sidebar', ['current' => 'dashboard'])
@endsection

@section('breadcrumb')
  System
@endsection

@section('content')
  <x-webkernel::page title="System">
    <p>System Admin Panel.</p>
  </x-webkernel::page>
@endsection
