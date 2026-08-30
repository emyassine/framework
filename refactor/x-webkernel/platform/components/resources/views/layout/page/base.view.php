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
  $panel = \function_exists('webapp') ? \webapp()->panel()->matching_path() : null;
  $panel_id = \is_array($panel) ? (string) ($panel['id'] ?? '') : '';
  $primary_hex = '';
  $header_css = '';
  $footer_js = '';
  if ($panel_id !== '') {
    $primary_hex = (string) \Webkernel\Config\Config::get('panels.'.$panel_id.'.color_primary', '');
    if ($theme === 'dark') {
      $dark = (string) \Webkernel\Config\Config::get('panels.'.$panel_id.'.color_primary_dark', '');
      if ($dark !== '') {
        $primary_hex = $dark;
      }
    }
    $header_css = (string) \Webkernel\Config\Config::get('panels.'.$panel_id.'.header_css', '');
    $footer_js = (string) \Webkernel\Config\Config::get('panels.'.$panel_id.'.footer_js', '');
  }
  $primary_style = \Webkernel\Platform\Colors\Color::panel_inline($primary_hex);
@endphp
<!DOCTYPE html>
<html
  lang="{{ $lang }}"
  dir="{{ \function_exists('i18n_direction') ? i18n_direction($lang) : 'ltr' }}"
  data-w-theme="{{ $theme }}"
  data-w-layout="{{ $layout }}"
  @if ($primary_style !== '') style="{{ $primary_style }}" @endif
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
  <link rel="stylesheet" href="{{ \Webkernel\Platform\Assets::css_href() }}">
  @if ($header_css !== '')
    <style>{!! $header_css !!}</style>
  @endif
  @stack('styles')
  @stack('head')
</head>
<body>
  {!! $slot !!}
  <script src="{{ \Webkernel\Platform\Assets::js_href() }}"></script>
  @if ($footer_js !== '')
    <script>{!! $footer_js !!}</script>
  @endif
  @stack('scripts')
</body>
</html>
