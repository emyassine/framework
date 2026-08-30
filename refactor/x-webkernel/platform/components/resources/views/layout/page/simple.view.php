{{--
  <x-webkernel::page.simple title="Sign in">…</x-webkernel::page.simple>
--}}
@props([
  'title' => 'Webkernel',
  'lang' => 'en',
  'theme' => null,
  'csrf' => true,
])
@once('wds.page.simple.view')
<style>
.wds-simple {
  width: 100%;
  max-width: 28rem;
  padding: 1.5rem;
  margin: 0 auto;
  color: var(--wds-text);
}
.wds-simple-card {
  background: var(--wds-surface);
  border: 1px solid var(--wds-border);
  border-radius: var(--wds-radius);
  padding: 2rem 1.75rem;
  color: var(--wds-text);
}
[data-wds-layout="simple"] body {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--wds-bg);
  color: var(--wds-text);
}
</style>
@endonce
<x-webkernel::page.base :title="$title" :lang="$lang" :theme="$theme" :csrf="$csrf" layout="simple">
  <main class="wds-simple">
    <div class="wds-simple-card">
      {!! $slot !!}
    </div>
  </main>
</x-webkernel::page.base>
