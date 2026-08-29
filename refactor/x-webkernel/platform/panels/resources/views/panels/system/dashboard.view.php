@extends('webkernel::layouts.page')

@section('title', 'System')

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
    <div style="margin-top:1rem;">
      <x-webkernel::button.group>
        <x-webkernel::button color="primary" size="md">Save</x-webkernel::button>
        <x-webkernel::button icon="plus" outlined>New</x-webkernel::button>
      </x-webkernel::button.group>
    </div>
  </x-webkernel::page>
@endsection
