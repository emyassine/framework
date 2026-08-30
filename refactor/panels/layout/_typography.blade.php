{{-- webkernel::panels.layout._typography — Webkernel Typography System (WTS) --}}
{{--
    FONTS multi-script: DM Sans | Lilex | IBM Plex Sans Arabic | Rubik | Amiri Quran
                        Noto Sans Hebrew/JP/SC/KR | Noto Nastaliq Urdu
    HEADINGS font-weight 400. CSS tokens --wts-*.
    SELF-HOST: php artisan webkernel:fetch-fonts → public/fetch-fonts/wts-fonts.css
    FILAMENT: overrides --font-family (Inter) after STYLES_AFTER; LocalFontProvider on panels.
--}}

@php
    $wts_local_css = \Webkernel\Typography\TypographySystem::local_css_url();
@endphp
@if ($wts_local_css)
    <link rel="stylesheet" href="{{ $wts_local_css }}" data-wts-fonts="local">
@else
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ \Webkernel\Typography\TypographySystem::google_css_url() }}" rel="stylesheet" data-wts-fonts="cdn">
@endif

@php
    $font_normal = 'var(--font-weight-normal, 400)';

    /*
     * RTL size boost — NEVER scale <html> (breaks Floating UI / language selector:
     * dropdown opens UP or fails when root font-size > 1).
     *
     * $bigger_on_rtl     → enable/disable
     * $bigger_on_rtl_by  → content scale (1.10 = +10%) on .fi-main / sidebar only
     */
    $bigger_on_rtl = true;
    $bigger_on_rtl_by = (float) 1.07;

    $p_scale_ltr = 1.07;
    // Bare <p> optical bases vs --text-sm (then * bigger_on_rtl_by if enabled).
    $p_scale_ar = 1.06;
    $p_scale_he = 1.05;
    $p_scale_fa = 1.0612;
    $p_scale_ur = 1.0816;
    $p_scale_rtl_generic = 1.08;

    if ($bigger_on_rtl) {
        $p_scale_ar *= $bigger_on_rtl_by;
        $p_scale_he *= $bigger_on_rtl_by;
        $p_scale_fa *= $bigger_on_rtl_by;
        $p_scale_ur *= $bigger_on_rtl_by;
        $p_scale_rtl_generic *= $bigger_on_rtl_by;
    }

    $p_scale_ltr = round($p_scale_ltr, 4);
    $p_scale_ar = round($p_scale_ar, 4);
    $p_scale_he = round($p_scale_he, 4);
    $p_scale_fa = round($p_scale_fa, 4);
    $p_scale_ur = round($p_scale_ur, 4);
    $p_scale_rtl_generic = round($p_scale_rtl_generic, 4);
    $content_scale_rtl = $bigger_on_rtl ? round($bigger_on_rtl_by, 4) : 1.0;

    $elements = [
        'h1' => [
            'font-size' => 'calc(var(--text-3xl) * 1.5)',
            'line-height' => '1.2',
            'letter-spacing' => '-0.025em',
            'font-weight' => $font_normal,
            'color' => 'var(--prose-heading-color)',
        ],
        'h2' => [
            'font-size' => 'var(--text-2xl)',
            'line-height' => '1.33333',
            'letter-spacing' => '-0.025em',
            'font-weight' => $font_normal,
            'color' => 'var(--prose-heading-color)',
        ],
        'h3' => [
            'font-size' => 'var(--text-xl)',
            'font-weight' => $font_normal,
            'color' => 'var(--prose-heading-color)',
        ],
        'h4' => [
            'font-size' => 'var(--text-lg)',
            'font-weight' => $font_normal,
            'color' => 'var(--prose-heading-color)',
        ],
        'h5' => [
            'font-size' => 'var(--text-base)',
            'font-weight' => $font_normal,
            'color' => 'var(--prose-heading-color)',
        ],
        'h6' => [
            'font-size' => 'var(--text-sm)',
            'font-weight' => $font_normal,
            'color' => 'var(--prose-heading-color)',
        ],
        'p' => [
            'font-size' => 'calc(var(--text-sm) * '.$p_scale_ltr.') !important',
            'color' => 'var(--prose-color)',
            'font-weight' => $font_normal,
        ],
    ];
    $mdPrefix = 'md';
    $notFi = ":not([class*='fi-'])";
@endphp

<style id="webkernel-panel-typography">


/* ═══════════════════════════════════════════════════════════════════════════
   §2  COLOR + FONT STACK TOKENS
════════════════════════════════════════════════════════════════════════════ */
:root {
    --prose-color:         var(--color-gray-700, var(--gray-700, #374151));
    --prose-heading-color: var(--color-gray-950, var(--gray-950, #030712));

    /* Per-script families (reorder via :lang() — do not collapse to DM Sans only). */
    --wts-font-latin:  "DM Sans", ui-sans-serif, system-ui, sans-serif;
    --wts-font-arabic: "IBM Plex Sans Arabic", "Rubik", "Noto Naskh Arabic", ui-sans-serif, sans-serif;
    --wts-font-hebrew: "Noto Sans Hebrew", "David Libre", ui-sans-serif, sans-serif;
    --wts-font-cjk:    "Noto Sans JP", "Noto Sans SC", "Noto Sans KR", "Hiragino Sans", "PingFang SC", sans-serif;
    --wts-font-urdu:   "Noto Nastaliq Urdu", "IBM Plex Sans Arabic", ui-sans-serif, sans-serif;
    --wts-font-mono:   "Lilex", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;

    /*
     * Default cascade: Latin first for UI chrome, then other scripts so the
     * browser can fall through per glyph when content is mixed (en + ar, etc.).
     */
    --wts-font-stack:
        var(--wts-font-latin),
        var(--wts-font-arabic),
        var(--wts-font-hebrew),
        var(--wts-font-cjk),
        sans-serif;

    /*
     * Kill Filament default Inter Variable.
     * base.blade.php sets --font-family: 'Inter Variable' — we override it here
     * (STYLES_AFTER) so every fi-* component using var(--font-family) gets WTS.
     */
    --font-family: var(--wts-font-stack) !important;
    --mono-font-family: var(--wts-font-mono) !important;

    /* Paragraph scales (see $bigger_on_rtl / $bigger_on_rtl_by in Blade). */
    --wts-p-scale: {{ $p_scale_ltr }};
    --wts-p-scale-rtl: {{ $p_scale_rtl_generic }};
    --wts-p-scale-ar: {{ $p_scale_ar }};
    --wts-p-scale-he: {{ $p_scale_he }};
    --wts-p-scale-fa: {{ $p_scale_fa }};
    --wts-p-scale-ur: {{ $p_scale_ur }};
    /*
     * Content-only RTL boost (never on <html> — breaks Floating UI / dropdowns).
     * Topbar + language selector stay 1em; main + sidebar content scale.
     */
    --wts-content-scale-rtl: {{ $content_scale_rtl }};
}
.dark,
[data-theme="dark"],
.fi-body.dark,
:where(.dark, .dark *) {
    --prose-color:         var(--color-gray-300, var(--gray-300, #d1d5db));
    --prose-heading-color: var(--color-white, #fff);
}


/* ═══════════════════════════════════════════════════════════════════════════
   §3  GLOBAL MULTI-SCRIPT FONT-FAMILY
   Prefer CSS variables Filament already uses (--font-family), not Inter.
════════════════════════════════════════════════════════════════════════════ */
html,
html.fi,
body,
.fi-body {
    font-family: var(--font-family, var(--wts-font-stack));
    font-optical-sizing: auto;
    font-weight: var(--font-weight-normal, 400);
}

code, kbd, pre, samp,
.fi-mono,
.font-mono {
    font-family: var(--mono-font-family, var(--wts-font-mono));
}

.fi-header-subheading {
    margin-top: calc(var(--spacing) * 1) !important;
    font-size: calc(var(--text-lg) / 1.08);
}

/* Filament page title — keep normal weight (no bold H1) */
.fi-header-heading,
.fi-section-header-heading {
    font-weight: var(--font-weight-normal, 400) !important;
    font-style:  normal;
    font-family: var(--font-family, var(--wts-font-stack));
}

/* ═══════════════════════════════════════════════════════════════════════════
   §4  HEADING / PARAGRAPH METRICS
   Two selector groups per element:
     A) bare tag, excluding fi-* chrome  → content areas only
     B) .md-* hooks                      → MD-parser output, always wins
════════════════════════════════════════════════════════════════════════════ */
@foreach ($elements as $name => $props)
/* — {{ $name }} — */
{{ $name }}{!! $notFi !!} {
@foreach ($props as $prop => $value)
    {{ $prop }}: {{ $value }};
@endforeach
}
.{{ $mdPrefix }}-{{ $name }}@if (str_starts_with($name, 'h')),
{{ $name }}.{{ $mdPrefix }}-{{ $name }},
.{{ $mdPrefix }}-heading.{{ $mdPrefix }}-{{ $name }}@endif {
@foreach ($props as $prop => $value)
    {{ $prop }}: {{ $value }} !important;
@endforeach
}
@endforeach


/* ═══════════════════════════════════════════════════════════════════════════
   §5  PER-SCRIPT / RTL TYPOGRAPHY
   ─────────────────────────────────────────────────────────────────────────
   Prefer document language (html[lang], :lang()) to reorder the stack.
   Glyph fallback still works from --wts-font-stack when lang is wrong/missing.
════════════════════════════════════════════════════════════════════════════ */

/* — Base RTL — */
[dir="rtl"],
:lang(ar),
:lang(fa),
:lang(ur),
:lang(he) {
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
}

[dir="rtl"],
:lang(ar),
:lang(fa),
:lang(ur),
:lang(he) {
    direction: rtl;
    text-align: start; /* logical — respects dir */
}

/*
 * RTL content scale — NOT on <html>/<body>/topbar.
 * Root rem scale breaks Floating UI (language selector opens upward / stuck).
 * Scope: main content + sidebar nav only.
 */
html[lang|="ar"] .fi-main,
html[lang|="ar"] .fi-page,
html[lang|="ar"] .fi-simple-main,
html[lang|="ar"] .fi-sidebar-nav,
html[lang|="fa"] .fi-main,
html[lang|="fa"] .fi-page,
html[lang|="fa"] .fi-simple-main,
html[lang|="fa"] .fi-sidebar-nav,
html[lang|="he"] .fi-main,
html[lang|="he"] .fi-page,
html[lang|="he"] .fi-simple-main,
html[lang|="he"] .fi-sidebar-nav,
html[lang|="ur"] .fi-main,
html[lang|="ur"] .fi-page,
html[lang|="ur"] .fi-simple-main,
html[lang|="ur"] .fi-sidebar-nav,
html[dir="rtl"] .fi-main,
html[dir="rtl"] .fi-page,
html[dir="rtl"] .fi-simple-main,
html[dir="rtl"] .fi-sidebar-nav {
    font-size: calc(1em * var(--wts-content-scale-rtl, 1));
}

/* Teleported dropdowns live under <body> — force normal size (not root-scaled). */
.fi-dropdown-panel {
    font-size: 1rem;
}

/* — Arabic / Farsi (UI first: IBM Plex Sans Arabic) — */
:lang(ar),
:lang(fa),
html[lang|="ar"],
html[lang|="fa"],
[dir="rtl"]:lang(ar),
[dir="rtl"]:lang(fa) {
    --wts-font-stack:
        var(--wts-font-arabic),
        var(--wts-font-latin),
        var(--wts-font-hebrew),
        var(--wts-font-cjk),
        sans-serif;
    --font-family: var(--wts-font-stack) !important;
    font-family: var(--font-family);
    letter-spacing: 0; /* never track cursive script */
}

/* — Generic RTL body boost ([dir=rtl] without a more specific :lang rule) — */
[dir="rtl"] p{!! $notFi !!},
[dir="rtl"] .md-p {
    font-size: calc(var(--text-sm) * var(--wts-p-scale-rtl)) !important;
}

/* — Arabic body optical compensation (+ optional $bigger_on_rtl) — */
:lang(ar) p{!! $notFi !!},
:lang(ar) .md-p,
html[lang|="ar"] p{!! $notFi !!} {
    font-family: "Rubik", "IBM Plex Sans Arabic", var(--wts-font-latin);
    font-size:   calc(var(--text-sm) * var(--wts-p-scale-ar)) !important;
    line-height: 1.9;
    letter-spacing: 0;
    font-weight: var(--font-weight-normal, 400);
}

/* — Farsi body — */
:lang(fa) p{!! $notFi !!},
:lang(fa) .md-p,
html[lang|="fa"] p{!! $notFi !!} {
    font-family: "Rubik", "IBM Plex Sans Arabic", var(--wts-font-latin);
    font-size:   calc(var(--text-sm) * var(--wts-p-scale-fa)) !important;
    line-height: 1.9;
    letter-spacing: 0;
    font-weight: var(--font-weight-normal, 400);
}

/* — Arabic / Farsi headings (normal weight, Arabic UI face) — */
:lang(ar) :is(h1, h2, h3, h4, h5, h6){!! $notFi !!},
:lang(ar) :is(.md-h1, .md-h2, .md-h3, .md-h4, .md-h5, .md-h6),
:lang(fa) :is(h1, h2, h3, h4, h5, h6){!! $notFi !!},
:lang(fa) :is(.md-h1, .md-h2, .md-h3, .md-h4, .md-h5, .md-h6),
html[lang|="ar"] :is(h1, h2, h3, h4, h5, h6){!! $notFi !!},
html[lang|="fa"] :is(h1, h2, h3, h4, h5, h6){!! $notFi !!} {
    font-family: "IBM Plex Sans Arabic", var(--wts-font-latin);
    letter-spacing: 0;
    line-height: 1.4;
    font-weight: var(--font-weight-normal, 400);
}

/* — Qur'an / formal Arabic prose — */
.ar-quran,
.ar-formal {
    font-family: "Amiri Quran", "IBM Plex Sans Arabic", serif;
    font-size:   1.15em;
    line-height: 2.2;
    letter-spacing: 0;
    direction:   rtl;
    text-align:  start;
    font-weight: var(--font-weight-normal, 400);
}

/* — Hebrew — */
:lang(he),
html[lang|="he"] {
    --wts-font-stack:
        var(--wts-font-hebrew),
        var(--wts-font-latin),
        var(--wts-font-arabic),
        var(--wts-font-cjk),
        sans-serif;
    --font-family: var(--wts-font-stack) !important;
    font-family: var(--font-family);
    letter-spacing: 0;
}
:lang(he) p{!! $notFi !!},
:lang(he) .md-p,
html[lang|="he"] p{!! $notFi !!} {
    font-family: var(--wts-font-hebrew);
    font-size: calc(var(--text-sm) * var(--wts-p-scale-he)) !important;
    line-height: 1.85;
    letter-spacing: 0;
    font-weight: var(--font-weight-normal, 400);
}

/* — Japanese — */
:lang(ja),
html[lang|="ja"] {
    --wts-font-stack:
        "Noto Sans JP",
        var(--wts-font-latin),
        var(--wts-font-cjk),
        sans-serif;
    --font-family: var(--wts-font-stack) !important;
    font-family: var(--font-family);
    letter-spacing: 0.02em;
    line-height: 1.7;
}

/* — Chinese (Simplified) — */
:lang(zh),
:lang(zh-CN),
:lang(zh-Hans),
html[lang|="zh"] {
    --wts-font-stack:
        "Noto Sans SC",
        var(--wts-font-latin),
        var(--wts-font-cjk),
        sans-serif;
    --font-family: var(--wts-font-stack) !important;
    font-family: var(--font-family);
    letter-spacing: 0;
    line-height: 1.75;
}

/* — Korean — */
:lang(ko),
html[lang|="ko"] {
    --wts-font-stack:
        "Noto Sans KR",
        var(--wts-font-latin),
        var(--wts-font-cjk),
        sans-serif;
    --font-family: var(--wts-font-stack) !important;
    font-family: var(--font-family);
    letter-spacing: 0;
    line-height: 1.7;
}

/* — Urdu (nastaliq) — */
:lang(ur),
html[lang|="ur"],
.ur-nastaliq {
    --wts-font-stack: var(--wts-font-urdu), var(--wts-font-arabic), var(--wts-font-latin), sans-serif;
    --font-family: var(--wts-font-stack) !important;
    font-family: var(--font-family);
    line-height: 2.4;
    letter-spacing: 0;
    direction: rtl;
    text-align: start;
}
:lang(ur) p{!! $notFi !!},
:lang(ur) .md-p,
html[lang|="ur"] p{!! $notFi !!} {
    font-size: calc(var(--text-sm) * var(--wts-p-scale-ur)) !important;
}

/* — Latin runs inside Arabic/Hebrew: restore DM Sans for nested :lang(en) — */
:lang(ar) :lang(en),
:lang(fa) :lang(en),
:lang(he) :lang(en) {
    font-family: var(--wts-font-latin);
    letter-spacing: normal;
}


/* ═══════════════════════════════════════════════════════════════════════════
   §6  SPACING SYSTEM
   Mirrors Filament .fi-prose — but margin lives on the element itself so it
   works outside .fi-prose (tables, callouts, .fi-not-prose, …).
════════════════════════════════════════════════════════════════════════════ */

/* — Zero out block margin on all prose elements — */
:is(
    h1{!! $notFi !!}, h2{!! $notFi !!}, h3{!! $notFi !!},
    h4{!! $notFi !!}, h5{!! $notFi !!}, h6{!! $notFi !!},
    p{!! $notFi !!},
    .{{ $mdPrefix }}-h1, .{{ $mdPrefix }}-h2, .{{ $mdPrefix }}-h3,
    .{{ $mdPrefix }}-h4, .{{ $mdPrefix }}-h5, .{{ $mdPrefix }}-h6,
    .{{ $mdPrefix }}-p
) {
    margin-block: 0;
}

/* — Space AFTER every heading (before body, tables, callouts, …) — */
:is(
    h1{!! $notFi !!}, h2{!! $notFi !!}, h3{!! $notFi !!},
    h4{!! $notFi !!}, h5{!! $notFi !!}, h6{!! $notFi !!},
    .{{ $mdPrefix }}-h1, .{{ $mdPrefix }}-h2, .{{ $mdPrefix }}-h3,
    .{{ $mdPrefix }}-h4, .{{ $mdPrefix }}-h5, .{{ $mdPrefix }}-h6
) {
    margin-bottom: calc(var(--spacing, 0.25rem) * 4);
}

/* — Space BEFORE section headings (h2+) — */
:is(
    h2{!! $notFi !!}, h3{!! $notFi !!}, h4{!! $notFi !!},
    h5{!! $notFi !!}, h6{!! $notFi !!},
    .{{ $mdPrefix }}-h2, .{{ $mdPrefix }}-h3, .{{ $mdPrefix }}-h4,
    .{{ $mdPrefix }}-h5, .{{ $mdPrefix }}-h6
) {
    margin-top: calc(var(--spacing, 0.25rem) * 8);
}

/* — p + p gap — */
:is(p{!! $notFi !!}, .{{ $mdPrefix }}-p)
  + :is(p{!! $notFi !!}, .{{ $mdPrefix }}-p) {
    margin-top: calc(var(--spacing, 0.25rem) * 4);
}

/* — heading → p: heading already has margin-bottom, skip double gap — */
:is(
    h1{!! $notFi !!}, h2{!! $notFi !!}, h3{!! $notFi !!},
    h4{!! $notFi !!}, h5{!! $notFi !!}, h6{!! $notFi !!},
    .{{ $mdPrefix }}-h1, .{{ $mdPrefix }}-h2, .{{ $mdPrefix }}-h3,
    .{{ $mdPrefix }}-h4, .{{ $mdPrefix }}-h5, .{{ $mdPrefix }}-h6
)
  + :is(p{!! $notFi !!}, .{{ $mdPrefix }}-p) {
    margin-top: 0;
}

/* — hr → h2: explicit section break spacing — */
hr + h2{!! $notFi !!},
hr + .{{ $mdPrefix }}-h2,
.{{ $mdPrefix }}-hr + h2{!! $notFi !!},
.{{ $mdPrefix }}-hr + .{{ $mdPrefix }}-h2 {
    margin-top: calc(var(--spacing, 0.25rem) * 8);
}

/* ─── Lists ─────────────────────────────────────────────────────────────── */
:is(
    ul{!! $notFi !!}, ol{!! $notFi !!},
    .{{ $mdPrefix }}-list
) {
    margin-top:          calc(var(--spacing, 0.25rem) * 4);
    margin-bottom:       calc(var(--spacing, 0.25rem) * 4);
    padding-inline-start: calc(var(--spacing, 0.25rem) * 6);
}

ul{!! $notFi !!},
.{{ $mdPrefix }}-list.{{ $mdPrefix }}-list-unordered {
    list-style-type: disc;
}

ol{!! $notFi !!},
.{{ $mdPrefix }}-list.{{ $mdPrefix }}-list-ordered {
    list-style-type: decimal;
}

:is(
    h1{!! $notFi !!}, h2{!! $notFi !!}, h3{!! $notFi !!},
    h4{!! $notFi !!}, h5{!! $notFi !!}, h6{!! $notFi !!},
    .{{ $mdPrefix }}-h1, .{{ $mdPrefix }}-h2, .{{ $mdPrefix }}-h3,
    .{{ $mdPrefix }}-h4, .{{ $mdPrefix }}-h5, .{{ $mdPrefix }}-h6
)
  + :is(ul{!! $notFi !!}, ol{!! $notFi !!}, .{{ $mdPrefix }}-list) {
    margin-top: 0;
}

:is(ul{!! $notFi !!}, ol{!! $notFi !!}, .{{ $mdPrefix }}-list)
  + :is(p{!! $notFi !!}, .{{ $mdPrefix }}-p) {
    margin-top: 0;
}

:is(ul{!! $notFi !!}, ol{!! $notFi !!}, .{{ $mdPrefix }}-list)
  > :is(li{!! $notFi !!}, .{{ $mdPrefix }}-list-item) {
    margin-top:           0;
    margin-bottom:        0;
    padding-inline-start: calc(var(--spacing, 0.25rem) * 1);
}
:is(ul{!! $notFi !!}, ol{!! $notFi !!}, .{{ $mdPrefix }}-list)
  > :is(li{!! $notFi !!}, .{{ $mdPrefix }}-list-item)
  + :is(li{!! $notFi !!}, .{{ $mdPrefix }}-list-item) {
    margin-top: calc(var(--spacing, 0.25rem) * 2);
}

:is(ul{!! $notFi !!}, ol{!! $notFi !!}, .{{ $mdPrefix }}-list)
  :is(ul{!! $notFi !!}, ol{!! $notFi !!}, .{{ $mdPrefix }}-list) {
    margin-top:    calc(var(--spacing, 0.25rem) * 2);
    margin-bottom: calc(var(--spacing, 0.25rem) * 2);
}

.fi-prose > :first-child,
.md-prose > :first-child,
.md-root  > :first-child,
.fi-prose > .md-root > :first-child {
    margin-top: 0;
}


/* ═══════════════════════════════════════════════════════════════════════════
   §7  SCROLL-MARGIN (sticky header clearance)
════════════════════════════════════════════════════════════════════════════ */
:is(
    h2{!! $notFi !!}, h3{!! $notFi !!}, h4{!! $notFi !!},
    h5{!! $notFi !!}, h6{!! $notFi !!},
    .{{ $mdPrefix }}-h2, .{{ $mdPrefix }}-h3, .{{ $mdPrefix }}-h4,
    .{{ $mdPrefix }}-h5, .{{ $mdPrefix }}-h6
) {
    scroll-margin-top: calc(var(--spacing, 0.25rem) * 32);
}

@@media (min-width: 64rem) {
    :is(
        h2{!! $notFi !!}, h3{!! $notFi !!}, h4{!! $notFi !!},
        h5{!! $notFi !!}, h6{!! $notFi !!},
        .{{ $mdPrefix }}-h2, .{{ $mdPrefix }}-h3, .{{ $mdPrefix }}-h4,
        .{{ $mdPrefix }}-h5, .{{ $mdPrefix }}-h6
    ) {
        scroll-margin-top: calc(var(--spacing, 0.25rem) * 18);
    }
}


</style>
