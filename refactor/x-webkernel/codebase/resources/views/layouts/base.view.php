@props([
  'lang' => 'en',
  'theme' => 'light',
  'layout' => 'base',
  'title' => 'Webkernel',
  'csrf' => true,
])
<!DOCTYPE html>
<html
  lang="{{ $lang }}"
  data-wds-theme="{{ $theme }}"
  data-wds-layout="{{ $layout }}"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  @if ($csrf)
    {!! \Webkernel\Csrf::meta() !!}
  @endif
  <title>{{ $title }}</title>
  @include('webkernel::layouts.partials.typography')
  <link rel="stylesheet" href="{{ \Webkernel\Platform\Wds::css_href() }}">
  @stack('styles')
  @stack('head')
</head>
<body>
  @yield('content')
  @stack('scripts')
</body>
</html>
