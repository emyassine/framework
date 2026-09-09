{{--
  Sign in. Centered. No rail.
--}}
@props([
  'error' => '',
  'email' => '',
])
@php
  $logo = \function_exists('webkernel_branding_url') ? webkernel_branding_url('webkernel-favicon') : '';
@endphp
<x-webkernel::page.simple title="Sign in">
  <form class="w-login" method="post" action="/login">
    @csrf
    <div class="w-login-brand">
      @if ($logo !== '')
        <img src="{{ $logo }}" alt="" width="40" height="40" />
      @endif
    </div>
    <h1 class="w-login-title">Sign in</h1>
    @if ($error !== '')
      <p class="w-login-error" role="alert">{{ $error }}</p>
    @endif
    <x-webkernel::text-input name="email" label="Email" type="email" :value="$email" />
    <x-webkernel::text-input name="password" label="Password" type="password" />
    <div class="w-login-actions">
      <x-webkernel::button type="submit" color="primary">Sign in</x-webkernel::button>
    </div>
  </form>
</x-webkernel::page.simple>
