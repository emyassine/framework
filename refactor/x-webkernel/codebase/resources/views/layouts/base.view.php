<!DOCTYPE html>
<html
  lang="{{ $lang ?? 'en' }}"
  data-webkernel-design-theme="{{ $theme ?? 'light' }}"
  data-webkernel-design-layout="{{ $layout ?? 'base' }}"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $title ?? 'Webkernel' }}</title>
  @include('webkernel::layouts.partials.tokens')
  @stack('styles')
</head>
<body>
  @yield('content')
  @stack('scripts')
</body>
</html>
