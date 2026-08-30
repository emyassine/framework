{{--
  Document. <x-webkernel::page> and <x-webkernel::page.simple> wrap this.
--}}
@props([
  'lang' => 'en',
  'theme' => null,
  'title' => 'Webkernel',
  'description' => null,
  'csrf' => true,
  'favicon' => null,
  'layout' => 'base',
])
@php
  $lang = \function_exists('i18n_current_lang') ? i18n_current_lang() : ($lang ?? 'en');
  $theme = $theme ?? (\Webkernel\Config\Config::get('ui.dark_mode', true) ? 'dark' : 'light');
  $description = $description ?? (string) $title;
  if ($favicon === null && \function_exists('webkernel_branding_url')) {
    $favicon = webkernel_branding_url('webkernel-favicon');
  }
@endphp
<!DOCTYPE html>
<html
  lang="{{ $lang }}"
  dir="{{ \function_exists('i18n_direction') ? i18n_direction($lang) : 'ltr' }}"
  data-w-theme="{{ $theme }}"
  data-w-layout="{{ $layout }}"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script>
  	(function(d){var t=localStorage.getItem('w-theme');if(t)d.dataset.wTheme=t;})(document.documentElement);
  </script>
  @if ($csrf)
    {!! \Webkernel\Csrf::meta() !!}
  @endif
  @if (!empty($favicon))
    <link rel="icon" href="{{ $favicon }}" />
  @endif
  <title>{{ $title }}</title>
  <meta name="description" content="{{ $description }}" />
  <x-webkernel::typography :lang="$lang" />
  <link rel="stylesheet" href="{{ \Webkernel\Platform\Wds::css_href() }}">
  @stack('styles')
  @stack('head')
</head>
<body>
  {!! $slot !!}
  @stack('scripts')
</body>
</html>
