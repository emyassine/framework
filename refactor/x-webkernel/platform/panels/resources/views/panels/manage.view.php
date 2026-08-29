@extends('webkernel::layouts.page')

@section('title', lang('panel.manage'))

@section('breadcrumb')
  {{ $panel['label'] ?? '' }} / {{ lang('panel.manage') }}
@endsection

@section('content')
  <x-webkernel::page :title="lang('panel.manage')" :description="lang('panel.manage_help')">
    @if (!empty($saved))
      <p class="wds-flash">{{ lang('panel.saved') }}</p>
    @endif
    <form class="wds-form" method="post" action="">
      {!! \Webkernel\Csrf::field() !!}
      <div class="wds-form-row">
        <label for="panel-logo">{{ lang('panel.logo') }}</label>
        <input id="panel-logo" type="url" name="logo" value="{{ $logo }}" placeholder="https://" />
      </div>
      <div class="wds-form-row">
        <label for="panel-logo-shape">{{ lang('panel.logo_shape') }}</label>
        <select id="panel-logo-shape" name="logo_shape">
          <option value="favicon"{{ ($logo_shape ?? '') === 'favicon' ? ' selected' : '' }}>{{ lang('panel.logo_shape_favicon') }}</option>
          <option value="round"{{ ($logo_shape ?? '') === 'round' ? ' selected' : '' }}>{{ lang('panel.logo_shape_round') }}</option>
          <option value="square"{{ ($logo_shape ?? '') === 'square' ? ' selected' : '' }}>{{ lang('panel.logo_shape_square') }}</option>
        </select>
      </div>
      <div class="wds-form-actions">
        <x-webkernel::button type="submit" color="primary">{{ lang('panel.save') }}</x-webkernel::button>
      </div>
    </form>
  </x-webkernel::page>
@endsection
