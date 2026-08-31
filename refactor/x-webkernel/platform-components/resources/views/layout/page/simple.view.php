{{--
  <x-webkernel::page.simple title="Sign in">…</x-webkernel::page.simple>
--}}
@props([
  'title' => 'Webkernel',
  'lang' => 'en',
  'theme' => null,
  'csrf' => true,
])
<x-webkernel::page.base :title="$title" :lang="$lang" :theme="$theme" :csrf="$csrf" layout="simple">
  <main class="w-simple">
    <div class="w-simple-card">
      {!! $slot !!}
    </div>
  </main>
</x-webkernel::page.base>
