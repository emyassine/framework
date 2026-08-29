@props([
  'lang' => 'en',
  'theme' => 'light',
  'title' => 'Webkernel',
  'csrf' => true,
])
<!DOCTYPE html>
<html
  lang="{{ $lang }}"
  data-wds-theme="{{ $theme }}"
  data-wds-layout="simple"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  @if ($csrf)
    {!! \Webkernel\Csrf::meta() !!}
  @endif
  <title>{{ $title }}</title>
  <meta name="description" content="{{ $title }}" />
  @include('webkernel::layouts.partials.typography')
  <link rel="stylesheet" href="{{ \Webkernel\Platform\Wds::css_href() }}">
  @stack('styles')
  @stack('head')
</head>
<body>
  <main class="wds-simple">
    <div class="wds-simple-card">
      @yield('content')
    </div>
  </main>
  @stack('scripts')
</body>
</html>
