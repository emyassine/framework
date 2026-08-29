@php
  $wts_fonts = \Webkernel\Typography\TypographySystem::fonts_href();
  $wts_rules = \Webkernel\Typography\TypographySystem::rules_href();
  $wts_local = \Webkernel\Typography\TypographySystem::has_local_fonts();
@endphp
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
