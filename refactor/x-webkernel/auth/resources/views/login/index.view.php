{{--
  Sign in. Centered. No rail.
--}}
@props([
  'error' => '',
  'email' => '',
])
@once('wds.login')
<style>
.wds-login { display: flex; flex-direction: column; gap: 1rem; color: var(--wds-text); }
.wds-login-brand { display: flex; justify-content: center; margin-bottom: 0.5rem; }
.wds-login-brand img { width: 2.5rem; height: 2.5rem; object-fit: contain; }
.wds-login-title { font-size: var(--wds-text-xl); font-weight: 600; text-align: center; color: var(--wds-text); }
.wds-login-error {
  padding: 0.6rem 0.75rem;
  border-radius: var(--wds-radius);
  background: var(--danger-50);
  color: var(--danger-800);
  font-size: var(--wds-text-sm);
}
.wds-login-actions { margin-top: 0.5rem; display: flex; }
.wds-login-actions .wds-btn { width: 100%; justify-content: center; }
</style>
@endonce
@php
  $logo = \function_exists('webkernel_branding_url') ? webkernel_branding_url('webkernel-favicon') : '';
@endphp
<x-webkernel::page.simple title="Sign in">
  <form class="wds-login" method="post" action="/login">
    @csrf
    <div class="wds-login-brand">
      @if ($logo !== '')
        <img src="{{ $logo }}" alt="" width="40" height="40" />
      @endif
    </div>
    <h1 class="wds-login-title">Sign in</h1>
    @if ($error !== '')
      <p class="wds-login-error" role="alert">{{ $error }}</p>
    @endif
    <x-webkernel::text-input name="email" label="Email" type="email" :value="$email" />
    <x-webkernel::text-input name="password" label="Password" type="password" />
    <div class="wds-login-actions">
      <x-webkernel::button type="submit" color="primary">Sign in</x-webkernel::button>
    </div>
  </form>
</x-webkernel::page.simple>
