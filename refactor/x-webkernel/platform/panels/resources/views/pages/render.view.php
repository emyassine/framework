@php
  $title = (string) ($title ?? '');
  $header = (string) ($header ?? $title);
  $subheader = (string) ($subheader ?? '');
  $header_actions = \is_array($header_actions ?? null) ? $header_actions : [];
  $breadcrumbs = \is_array($breadcrumbs ?? null) ? $breadcrumbs : [];
@endphp
@extends('webkernel::layouts.page')

@section('title', $title)

@section('breadcrumb')
  @foreach ($breadcrumbs as $i => $crumb)
    @php
      $label = \is_array($crumb) ? (string) ($crumb['label'] ?? '') : (string) $crumb;
      $href = \is_array($crumb) ? (string) ($crumb['href'] ?? '') : '';
    @endphp
    @if ($i > 0)
      <span aria-hidden="true">/</span>
    @endif
    @if ($href !== '')
      <a href="{{ $href }}">{{ $label }}</a>
    @else
      <span>{{ $label }}</span>
    @endif
  @endforeach
@endsection

@section('content')
  <x-webkernel::page :title="$header" :description="$subheader !== '' ? $subheader : null" :csrf="false">
    @if ($header_actions !== [])
      @slot('actions')
        @foreach ($header_actions as $action)
          {!! $action !!}
        @endforeach
      @endslot
    @endif
    {!! $slot !!}
  </x-webkernel::page>
@endsection
