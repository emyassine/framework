<!DOCTYPE html>
<html
  lang="{{ $lang ?? 'en' }}"
  data-wds-theme="{{ $theme ?? 'light' }}"
  data-wds-layout="simple"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $title ?? 'Webkernel' }}</title>
  @include('layouts.partials.tokens')
  @include('layouts.partials.simple')
  @stack('styles')
</head>
<body>
  <main class="wks-simple">
    <div class="wks-simple__card">
      @yield('content')
    </div>
  </main>
  @stack('scripts')
</body>
</html>
