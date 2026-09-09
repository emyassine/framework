@php
  $wts_lang = $lang ?? 'en';
  $wts_fonts = \Webkernel\Typography\TypographySystem::fonts_href($wts_lang);
  $wts_rules = \Webkernel\Typography\TypographySystem::rules_href();
  $wts_local = \Webkernel\Typography\TypographySystem::has_local_fonts($wts_lang);
  $wts_preload = \Webkernel\Typography\TypographySystem::preload_href($wts_lang);
@endphp
@if ($wts_preload !== '')
  <link rel="preload" href="{{ $wts_preload }}" as="font" type="font/woff2" crossorigin>
@endif
@if ($wts_local)
  <link rel="stylesheet" href="{{ $wts_fonts }}" data-wts-fonts="local">
@else
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="{{ $wts_fonts }}" rel="stylesheet" data-wts-fonts="cdn">
@endif
@if ($wts_rules !== '')
  <link rel="stylesheet" href="{{ $wts_rules }}" data-wts-rules>
@endif
