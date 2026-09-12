@props([
  'error' => [],
])
@php
  $error = \is_array($error) ? $error : [];
  $code = (int) ($error['code'] ?? 500);
  $title = (string) ($error['title'] ?? \Webkernel\Errors\HttpError::reason($code));
  $description = (string) ($error['description'] ?? '');
@endphp
<x-webkernel::page.simple :title="$title">
  <section class="w-error-page" data-accent="{{ $error['accent'] ?? 'gray' }}">
    <div class="w-error-code">{{ $code }}</div>
    <h1>{{ $title }}</h1>
    @if ($description !== '')
      <p>{{ $description }}</p>
    @endif
  </section>
</x-webkernel::page.simple>
