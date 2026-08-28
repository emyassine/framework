<!DOCTYPE html>
<html
  lang="{{ $lang ?? 'en' }}"
  data-webkernel-design-theme="{{ $theme ?? 'light' }}"
  data-webkernel-design-layout="simple"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $title ?? 'Webkernel' }}</title>
  @include('webkernel::layouts.partials.tokens')
  @include('webkernel::layouts.partials.simple')
  @stack('styles')
</head>
<body>
  <main class="webkernel-shell-simple">
    <div class="webkernel-shell-simple__card">
      @yield('content')
    </div>
  </main>
  @stack('scripts')
</body>
</html>
