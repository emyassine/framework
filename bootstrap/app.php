<?php declare(strict_types=1);
require __DIR__ . '/../third_party/autoload.php';
if (getenv('WEBKERNEL_PROFILE_LIFECYCLE') === '1') {
    define('WEBKERNEL_T_AUTOLOAD', hrtime(true));
}
?>

<!DOCTYPE html>
<html lang="en" data-wds-theme="light" data-wds-layout="sidebar" data-wds-sidebar="expanded">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Webkernel — Dashboard</title>
  <style>
    /* ============================================================
       WEBKERNEL DESIGN SYSTEM (wds-)
       WEBKERNEL COLOR SYSTEM  (wcs-)
       Naming convention:
         wds- = design tokens (spacing, radius, shadow, typography)
         wcs- = color tokens (semantic + palette)
         wks- = component / shell tokens
    ============================================================ */

    /* ----------------------------------------------------------
       WCS — COLOR PALETTE (Filament-style OKLCH)
    ---------------------------------------------------------- */
    :root {

      /* — Danger — */
      --wcs-danger-50:  oklch(0.971 0.013 17.38);
      --wcs-danger-100: oklch(0.936 0.032 17.717);
      --wcs-danger-200: oklch(0.885 0.062 18.334);
      --wcs-danger-300: oklch(0.808 0.114 19.571);
      --wcs-danger-400: oklch(0.704 0.191 22.216);
      --wcs-danger-500: oklch(0.637 0.237 25.331);
      --wcs-danger-600: oklch(0.577 0.245 27.325);
      --wcs-danger-700: oklch(0.505 0.213 27.518);
      --wcs-danger-800: oklch(0.444 0.177 26.899);
      --wcs-danger-900: oklch(0.396 0.141 25.723);
      --wcs-danger-950: oklch(0.258 0.092 26.042);

      /* — Gray — */
      --wcs-gray-50:  oklch(0.985 0 0);
      --wcs-gray-100: oklch(0.967 0.001 286.375);
      --wcs-gray-200: oklch(0.92  0.004 286.32);
      --wcs-gray-300: oklch(0.871 0.006 286.286);
      --wcs-gray-400: oklch(0.705 0.015 286.067);
      --wcs-gray-500: oklch(0.552 0.016 285.938);
      --wcs-gray-600: oklch(0.442 0.017 285.786);
      --wcs-gray-700: oklch(0.37  0.013 285.805);
      --wcs-gray-800: oklch(0.274 0.006 286.033);
      --wcs-gray-900: oklch(0.21  0.006 285.885);
      --wcs-gray-950: oklch(0.141 0.005 285.823);

      /* — Info / Primary — */
      --wcs-info-50:  oklch(0.97  0.014 254.604);
      --wcs-info-100: oklch(0.932 0.032 255.585);
      --wcs-info-200: oklch(0.882 0.059 254.128);
      --wcs-info-300: oklch(0.809 0.105 251.813);
      --wcs-info-400: oklch(0.707 0.165 254.624);
      --wcs-info-500: oklch(0.623 0.214 259.815);
      --wcs-info-600: oklch(0.546 0.245 262.881);
      --wcs-info-700: oklch(0.488 0.243 264.376);
      --wcs-info-800: oklch(0.424 0.199 265.638);
      --wcs-info-900: oklch(0.379 0.146 265.522);
      --wcs-info-950: oklch(0.282 0.091 267.935);

      --wcs-primary-50:  var(--wcs-info-50);
      --wcs-primary-100: var(--wcs-info-100);
      --wcs-primary-200: var(--wcs-info-200);
      --wcs-primary-300: var(--wcs-info-300);
      --wcs-primary-400: var(--wcs-info-400);
      --wcs-primary-500: var(--wcs-info-500);
      --wcs-primary-600: var(--wcs-info-600);
      --wcs-primary-700: var(--wcs-info-700);
      --wcs-primary-800: var(--wcs-info-800);
      --wcs-primary-900: var(--wcs-info-900);
      --wcs-primary-950: var(--wcs-info-950);

      /* — Success — */
      --wcs-success-50:  oklch(0.982 0.018 155.826);
      --wcs-success-100: oklch(0.962 0.044 156.743);
      --wcs-success-200: oklch(0.925 0.084 155.995);
      --wcs-success-300: oklch(0.871 0.15  154.449);
      --wcs-success-400: oklch(0.792 0.209 151.711);
      --wcs-success-500: oklch(0.723 0.219 149.579);
      --wcs-success-600: oklch(0.627 0.194 149.214);
      --wcs-success-700: oklch(0.527 0.154 150.069);
      --wcs-success-800: oklch(0.448 0.119 151.328);
      --wcs-success-900: oklch(0.393 0.095 152.535);
      --wcs-success-950: oklch(0.266 0.065 152.934);

      /* — Warning — */
      --wcs-warning-50:  oklch(0.987 0.022 95.277);
      --wcs-warning-100: oklch(0.962 0.059 95.617);
      --wcs-warning-200: oklch(0.924 0.12  95.746);
      --wcs-warning-300: oklch(0.879 0.169 91.605);
      --wcs-warning-400: oklch(0.828 0.189 84.429);
      --wcs-warning-500: oklch(0.769 0.188 70.08);
      --wcs-warning-600: oklch(0.666 0.179 58.318);
      --wcs-warning-700: oklch(0.555 0.163 48.998);
      --wcs-warning-800: oklch(0.473 0.137 46.201);
      --wcs-warning-900: oklch(0.414 0.112 45.904);
      --wcs-warning-950: oklch(0.279 0.077 45.635);

      /* — Extended Palette (TailwindCSS v4 / color-*) — */
      --color-red-50: oklch(97.1% 0.013 17.38);
      --color-red-100: oklch(93.6% 0.032 17.717);
      --color-red-200: oklch(88.5% 0.062 18.334);
      --color-red-300: oklch(80.8% 0.114 19.571);
      --color-red-400: oklch(70.4% 0.191 22.216);
      --color-red-500: oklch(63.7% 0.237 25.331);
      --color-red-600: oklch(57.7% 0.245 27.325);
      --color-red-700: oklch(50.5% 0.213 27.518);
      --color-red-800: oklch(44.4% 0.177 26.899);
      --color-red-900: oklch(39.6% 0.141 25.723);
      --color-red-950: oklch(25.8% 0.092 26.042);
      --color-orange-50: oklch(98% 0.016 73.684);
      --color-orange-100: oklch(95.4% 0.038 75.164);
      --color-orange-200: oklch(90.1% 0.076 70.697);
      --color-orange-300: oklch(83.7% 0.128 66.29);
      --color-orange-400: oklch(75% 0.183 55.934);
      --color-orange-500: oklch(70.5% 0.213 47.604);
      --color-orange-600: oklch(64.6% 0.222 41.116);
      --color-orange-700: oklch(55.3% 0.195 38.402);
      --color-orange-800: oklch(47% 0.157 37.304);
      --color-orange-900: oklch(40.8% 0.123 38.172);
      --color-orange-950: oklch(26.6% 0.079 36.259);
      --color-amber-50: oklch(98.7% 0.022 95.277);
      --color-amber-100: oklch(96.2% 0.059 95.617);
      --color-amber-200: oklch(92.4% 0.12 95.746);
      --color-amber-300: oklch(87.9% 0.169 91.605);
      --color-amber-400: oklch(82.8% 0.189 84.429);
      --color-amber-500: oklch(76.9% 0.188 70.08);
      --color-amber-600: oklch(66.6% 0.179 58.318);
      --color-amber-700: oklch(55.5% 0.163 48.998);
      --color-amber-800: oklch(47.3% 0.137 46.201);
      --color-amber-900: oklch(41.4% 0.112 45.904);
      --color-amber-950: oklch(27.9% 0.077 45.635);
      --color-yellow-50: oklch(98.7% 0.026 102.212);
      --color-yellow-100: oklch(97.3% 0.071 103.193);
      --color-yellow-200: oklch(94.5% 0.129 101.54);
      --color-yellow-300: oklch(90.5% 0.182 98.111);
      --color-yellow-400: oklch(85.2% 0.199 91.936);
      --color-yellow-500: oklch(79.5% 0.184 86.047);
      --color-yellow-600: oklch(68.1% 0.162 75.834);
      --color-yellow-700: oklch(55.4% 0.135 66.442);
      --color-yellow-800: oklch(47.6% 0.114 61.907);
      --color-yellow-900: oklch(42.1% 0.095 57.708);
      --color-yellow-950: oklch(28.6% 0.066 53.813);
      --color-lime-50: oklch(98.6% 0.031 120.757);
      --color-lime-100: oklch(96.7% 0.067 122.328);
      --color-lime-200: oklch(93.8% 0.127 124.321);
      --color-lime-300: oklch(89.7% 0.196 126.665);
      --color-lime-400: oklch(84.1% 0.238 128.85);
      --color-lime-500: oklch(76.8% 0.233 130.85);
      --color-lime-600: oklch(64.8% 0.2 131.684);
      --color-lime-700: oklch(53.2% 0.157 131.589);
      --color-lime-800: oklch(45.3% 0.124 130.933);
      --color-lime-900: oklch(40.5% 0.101 131.063);
      --color-lime-950: oklch(27.4% 0.072 132.109);
      --color-green-50: oklch(98.2% 0.018 155.826);
      --color-green-100: oklch(96.2% 0.044 156.743);
      --color-green-200: oklch(92.5% 0.084 155.995);
      --color-green-300: oklch(87.1% 0.15 154.449);
      --color-green-400: oklch(79.2% 0.209 151.711);
      --color-green-500: oklch(72.3% 0.219 149.579);
      --color-green-600: oklch(62.7% 0.194 149.214);
      --color-green-700: oklch(52.7% 0.154 150.069);
      --color-green-800: oklch(44.8% 0.119 151.328);
      --color-green-900: oklch(39.3% 0.095 152.535);
      --color-green-950: oklch(26.6% 0.065 152.934);
      --color-emerald-50: oklch(97.9% 0.021 166.113);
      --color-emerald-100: oklch(95% 0.052 163.051);
      --color-emerald-200: oklch(90.5% 0.093 164.15);
      --color-emerald-300: oklch(84.5% 0.143 164.978);
      --color-emerald-400: oklch(76.5% 0.177 163.223);
      --color-emerald-500: oklch(69.6% 0.17 162.48);
      --color-emerald-600: oklch(59.6% 0.145 163.225);
      --color-emerald-700: oklch(50.8% 0.118 165.612);
      --color-emerald-800: oklch(43.2% 0.095 166.913);
      --color-emerald-900: oklch(37.8% 0.077 168.94);
      --color-emerald-950: oklch(26.2% 0.051 172.552);
      --color-teal-50: oklch(98.4% 0.014 180.72);
      --color-teal-100: oklch(95.3% 0.051 180.801);
      --color-teal-200: oklch(91% 0.096 180.426);
      --color-teal-300: oklch(85.5% 0.138 181.071);
      --color-teal-400: oklch(77.7% 0.152 181.912);
      --color-teal-500: oklch(70.4% 0.14 182.503);
      --color-teal-600: oklch(60% 0.118 184.704);
      --color-teal-700: oklch(51.1% 0.096 186.391);
      --color-teal-800: oklch(43.7% 0.078 188.216);
      --color-teal-900: oklch(38.6% 0.063 188.416);
      --color-teal-950: oklch(27.7% 0.046 192.524);
      --color-cyan-50: oklch(98.4% 0.019 200.873);
      --color-cyan-100: oklch(95.6% 0.045 203.388);
      --color-cyan-200: oklch(91.7% 0.08 205.041);
      --color-cyan-300: oklch(86.5% 0.127 207.078);
      --color-cyan-400: oklch(78.9% 0.154 211.53);
      --color-cyan-500: oklch(71.5% 0.143 215.221);
      --color-cyan-600: oklch(60.9% 0.126 221.723);
      --color-cyan-700: oklch(52% 0.105 223.128);
      --color-cyan-800: oklch(45% 0.085 224.283);
      --color-cyan-900: oklch(39.8% 0.07 227.392);
      --color-cyan-950: oklch(30.2% 0.056 229.695);
      --color-sky-50: oklch(97.7% 0.013 236.62);
      --color-sky-100: oklch(95.1% 0.026 236.824);
      --color-sky-200: oklch(90.1% 0.058 230.902);
      --color-sky-300: oklch(82.8% 0.111 230.318);
      --color-sky-400: oklch(74.6% 0.16 232.661);
      --color-sky-500: oklch(68.5% 0.169 237.323);
      --color-sky-600: oklch(58.8% 0.158 241.966);
      --color-sky-700: oklch(50% 0.134 242.749);
      --color-sky-800: oklch(44.3% 0.11 240.79);
      --color-sky-900: oklch(39.1% 0.09 240.876);
      --color-sky-950: oklch(29.3% 0.066 243.157);
      --color-blue-50: oklch(97% 0.014 254.604);
      --color-blue-100: oklch(93.2% 0.032 255.585);
      --color-blue-200: oklch(88.2% 0.059 254.128);
      --color-blue-300: oklch(80.9% 0.105 251.813);
      --color-blue-400: oklch(70.7% 0.165 254.624);
      --color-blue-500: oklch(62.3% 0.214 259.815);
      --color-blue-600: oklch(54.6% 0.245 262.881);
      --color-blue-700: oklch(48.8% 0.243 264.376);
      --color-blue-800: oklch(42.4% 0.199 265.638);
      --color-blue-900: oklch(37.9% 0.146 265.522);
      --color-blue-950: oklch(28.2% 0.091 267.935);
      --color-indigo-50: oklch(96.2% 0.018 272.314);
      --color-indigo-100: oklch(93% 0.034 272.788);
      --color-indigo-200: oklch(87% 0.065 274.039);
      --color-indigo-300: oklch(78.5% 0.115 274.713);
      --color-indigo-400: oklch(67.3% 0.182 276.935);
      --color-indigo-500: oklch(58.5% 0.233 277.117);
      --color-indigo-600: oklch(51.1% 0.262 276.966);
      --color-indigo-700: oklch(45.7% 0.24 277.023);
      --color-indigo-800: oklch(39.8% 0.195 277.366);
      --color-indigo-900: oklch(35.9% 0.144 278.697);
      --color-indigo-950: oklch(25.7% 0.09 281.288);
      --color-violet-50: oklch(96.9% 0.016 293.756);
      --color-violet-100: oklch(94.3% 0.029 294.588);
      --color-violet-200: oklch(89.4% 0.057 293.283);
      --color-violet-300: oklch(81.1% 0.111 293.571);
      --color-violet-400: oklch(70.2% 0.183 293.541);
      --color-violet-500: oklch(60.6% 0.25 292.717);
      --color-violet-600: oklch(54.1% 0.281 293.009);
      --color-violet-700: oklch(49.1% 0.27 292.581);
      --color-violet-800: oklch(43.2% 0.232 292.759);
      --color-violet-900: oklch(38% 0.189 293.745);
      --color-violet-950: oklch(28.3% 0.141 291.089);
      --color-purple-50: oklch(97.7% 0.014 308.299);
      --color-purple-100: oklch(94.6% 0.033 307.174);
      --color-purple-200: oklch(90.2% 0.063 306.703);
      --color-purple-300: oklch(82.7% 0.119 306.383);
      --color-purple-400: oklch(71.4% 0.203 305.504);
      --color-purple-500: oklch(62.7% 0.265 303.9);
      --color-purple-600: oklch(55.8% 0.288 302.321);
      --color-purple-700: oklch(49.6% 0.265 301.924);
      --color-purple-800: oklch(43.8% 0.218 303.724);
      --color-purple-900: oklch(38.1% 0.176 304.987);
      --color-purple-950: oklch(29.1% 0.149 302.717);
      --color-fuchsia-50: oklch(97.7% 0.017 320.058);
      --color-fuchsia-100: oklch(95.2% 0.037 318.852);
      --color-fuchsia-200: oklch(90.3% 0.076 319.62);
      --color-fuchsia-300: oklch(83.3% 0.145 321.434);
      --color-fuchsia-400: oklch(74% 0.238 322.16);
      --color-fuchsia-500: oklch(66.7% 0.295 322.15);
      --color-fuchsia-600: oklch(59.1% 0.293 322.896);
      --color-fuchsia-700: oklch(51.8% 0.253 323.949);
      --color-fuchsia-800: oklch(45.2% 0.211 324.591);
      --color-fuchsia-900: oklch(40.1% 0.17 325.612);
      --color-fuchsia-950: oklch(29.3% 0.136 325.661);
      --color-pink-50: oklch(97.1% 0.014 343.198);
      --color-pink-100: oklch(94.8% 0.028 342.258);
      --color-pink-200: oklch(89.9% 0.061 343.231);
      --color-pink-300: oklch(82.3% 0.12 346.018);
      --color-pink-400: oklch(71.8% 0.202 349.761);
      --color-pink-500: oklch(65.6% 0.241 354.308);
      --color-pink-600: oklch(59.2% 0.249 0.584);
      --color-pink-700: oklch(52.5% 0.223 3.958);
      --color-pink-800: oklch(45.9% 0.187 3.815);
      --color-pink-900: oklch(40.8% 0.153 2.432);
      --color-pink-950: oklch(28.4% 0.109 3.907);
      --color-rose-50: oklch(96.9% 0.015 12.422);
      --color-rose-100: oklch(94.1% 0.03 12.58);
      --color-rose-200: oklch(89.2% 0.058 10.001);
      --color-rose-300: oklch(81% 0.117 11.638);
      --color-rose-400: oklch(71.2% 0.194 13.428);
      --color-rose-500: oklch(64.5% 0.246 16.439);
      --color-rose-600: oklch(58.6% 0.253 17.585);
      --color-rose-700: oklch(51.4% 0.222 16.935);
      --color-rose-800: oklch(45.5% 0.188 13.697);
      --color-rose-900: oklch(41% 0.159 10.272);
      --color-rose-950: oklch(27.1% 0.105 12.094);
      --color-slate-50: oklch(98.4% 0.003 247.858);
      --color-slate-100: oklch(96.8% 0.007 247.896);
      --color-slate-200: oklch(92.9% 0.013 255.508);
      --color-slate-300: oklch(86.9% 0.022 252.894);
      --color-slate-400: oklch(70.4% 0.04 256.788);
      --color-slate-500: oklch(55.4% 0.046 257.417);
      --color-slate-600: oklch(44.6% 0.043 257.281);
      --color-slate-700: oklch(37.2% 0.044 257.287);
      --color-slate-800: oklch(27.9% 0.041 260.031);
      --color-slate-900: oklch(20.8% 0.042 265.755);
      --color-slate-950: oklch(12.9% 0.042 264.695);
      --color-gray-50: oklch(98.5% 0.002 247.839);
      --color-gray-100: oklch(96.7% 0.003 264.542);
      --color-gray-200: oklch(92.8% 0.006 264.531);
      --color-gray-300: oklch(87.2% 0.01 258.338);
      --color-gray-400: oklch(70.7% 0.022 261.325);
      --color-gray-500: oklch(55.1% 0.027 264.364);
      --color-gray-600: oklch(44.6% 0.03 256.802);
      --color-gray-700: oklch(37.3% 0.034 259.733);
      --color-gray-800: oklch(27.8% 0.033 256.848);
      --color-gray-900: oklch(21% 0.034 264.665);
      --color-gray-950: oklch(13% 0.028 261.692);
      --color-zinc-50: oklch(98.5% 0 0);
      --color-zinc-100: oklch(96.7% 0.001 286.375);
      --color-zinc-200: oklch(92% 0.004 286.32);
      --color-zinc-300: oklch(87.1% 0.006 286.286);
      --color-zinc-400: oklch(70.5% 0.015 286.067);
      --color-zinc-500: oklch(55.2% 0.016 285.938);
      --color-zinc-600: oklch(44.2% 0.017 285.786);
      --color-zinc-700: oklch(37% 0.013 285.805);
      --color-zinc-800: oklch(27.4% 0.006 286.033);
      --color-zinc-900: oklch(21% 0.006 285.885);
      --color-zinc-950: oklch(14.1% 0.005 285.823);
      --color-neutral-50: oklch(98.5% 0 0);
      --color-neutral-100: oklch(97% 0 0);
      --color-neutral-200: oklch(92.2% 0 0);
      --color-neutral-300: oklch(87% 0 0);
      --color-neutral-400: oklch(70.8% 0 0);
      --color-neutral-500: oklch(55.6% 0 0);
      --color-neutral-600: oklch(43.9% 0 0);
      --color-neutral-700: oklch(37.1% 0 0);
      --color-neutral-800: oklch(26.9% 0 0);
      --color-neutral-900: oklch(20.5% 0 0);
      --color-neutral-950: oklch(14.5% 0 0);
      --color-stone-50: oklch(98.5% 0.001 106.423);
      --color-stone-100: oklch(97% 0.001 106.424);
      --color-stone-200: oklch(92.3% 0.003 48.717);
      --color-stone-300: oklch(86.9% 0.005 56.366);
      --color-stone-400: oklch(70.9% 0.01 56.259);
      --color-stone-500: oklch(55.3% 0.013 58.071);
      --color-stone-600: oklch(44.4% 0.011 73.639);
      --color-stone-700: oklch(37.4% 0.01 67.558);
      --color-stone-800: oklch(26.8% 0.007 34.298);
      --color-stone-900: oklch(21.6% 0.006 56.043);
      --color-stone-950: oklch(14.7% 0.004 49.25);
      --color-mauve-50: oklch(98.5% 0 0);
      --color-mauve-100: oklch(96% 0.003 325.6);
      --color-mauve-200: oklch(92.2% 0.005 325.62);
      --color-mauve-300: oklch(86.5% 0.012 325.68);
      --color-mauve-400: oklch(71.1% 0.019 323.02);
      --color-mauve-500: oklch(54.2% 0.034 322.5);
      --color-mauve-600: oklch(43.5% 0.029 321.78);
      --color-mauve-700: oklch(36.4% 0.029 323.89);
      --color-mauve-800: oklch(26.3% 0.024 320.12);
      --color-mauve-900: oklch(21.2% 0.019 322.12);
      --color-mauve-950: oklch(14.5% 0.008 326);
      --color-olive-50: oklch(98.8% 0.003 106.5);
      --color-olive-100: oklch(96.6% 0.005 106.5);
      --color-olive-200: oklch(93% 0.007 106.5);
      --color-olive-300: oklch(88% 0.011 106.6);
      --color-olive-400: oklch(73.7% 0.021 106.9);
      --color-olive-500: oklch(58% 0.031 107.3);
      --color-olive-600: oklch(46.6% 0.025 107.3);
      --color-olive-700: oklch(39.4% 0.023 107.4);
      --color-olive-800: oklch(28.6% 0.016 107.4);
      --color-olive-900: oklch(22.8% 0.013 107.4);
      --color-olive-950: oklch(15.3% 0.006 107.1);
      --color-mist-50: oklch(98.7% 0.002 197.1);
      --color-mist-100: oklch(96.3% 0.002 197.1);
      --color-mist-200: oklch(92.5% 0.005 214.3);
      --color-mist-300: oklch(87.2% 0.007 219.6);
      --color-mist-400: oklch(72.3% 0.014 214.4);
      --color-mist-500: oklch(56% 0.021 213.5);
      --color-mist-600: oklch(45% 0.017 213.2);
      --color-mist-700: oklch(37.8% 0.015 216);
      --color-mist-800: oklch(27.5% 0.011 216.9);
      --color-mist-900: oklch(21.8% 0.008 223.9);
      --color-mist-950: oklch(14.8% 0.004 228.8);
      --color-taupe-50: oklch(98.6% 0.002 67.8);
      --color-taupe-100: oklch(96% 0.002 17.2);
      --color-taupe-200: oklch(92.2% 0.005 34.3);
      --color-taupe-300: oklch(86.8% 0.007 39.5);
      --color-taupe-400: oklch(71.4% 0.014 41.2);
      --color-taupe-500: oklch(54.7% 0.021 43.1);
      --color-taupe-600: oklch(43.8% 0.017 39.3);
      --color-taupe-700: oklch(36.7% 0.016 35.7);
      --color-taupe-800: oklch(26.8% 0.011 36.5);
      --color-taupe-900: oklch(21.4% 0.009 43.1);
      --color-taupe-950: oklch(14.7% 0.004 49.3);
      --color-black: #000;
      --color-white: #fff;

      /* ----------------------------------------------------------
         WDS — DESIGN TOKENS (light theme defaults)
      ---------------------------------------------------------- */

      /* Semantic surface tokens */
      --wds-bg:            var(--wcs-gray-50);
      --wds-bg-subtle:     var(--wcs-gray-100);
      --wds-surface:       #ffffff;
      --wds-surface-raise: #ffffff;
      --wds-border:        var(--wcs-gray-200);
      --wds-border-strong: var(--wcs-gray-300);

      /* Semantic text tokens */
      --wds-text:          var(--wcs-gray-900);
      --wds-text-muted:    var(--wcs-gray-500);
      --wds-text-faint:    var(--wcs-gray-400);
      --wds-text-on-dark:  var(--wcs-gray-50);

      /* Semantic action tokens */
      --wds-accent:        var(--wcs-primary-600);
      --wds-accent-hover:  var(--wcs-primary-700);
      --wds-accent-subtle: var(--wcs-primary-50);
      --wds-accent-text:   var(--wcs-primary-700);

      /* Status tokens */
      --wds-success-bg:    var(--wcs-success-50);
      --wds-success-text:  var(--wcs-success-700);
      --wds-success-border:var(--wcs-success-200);
      --wds-warning-bg:    var(--wcs-warning-50);
      --wds-warning-text:  var(--wcs-warning-700);
      --wds-danger-bg:     var(--wcs-danger-50);
      --wds-danger-text:   var(--wcs-danger-700);
      --wds-info-bg:       var(--wcs-info-50);
      --wds-info-text:     var(--wcs-info-700);

      /* Sizing & spacing */
      --wds-radius-sm:  0.25rem;
      --wds-radius:     0.5rem;
      --wds-radius-lg:  0.75rem;
      --wds-radius-xl:  1rem;
      --wds-radius-full: 9999px;

      /* Shadow */
      --wds-shadow-sm:  0 1px 2px 0 oklch(0 0 0 / 0.05);
      --wds-shadow:     0 1px 3px 0 oklch(0 0 0 / 0.1), 0 1px 2px -1px oklch(0 0 0 / 0.1);
      --wds-shadow-md:  0 4px 6px -1px oklch(0 0 0 / 0.08), 0 2px 4px -2px oklch(0 0 0 / 0.08);

      /* Typography */
      --wds-font-sans:  system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      --wds-font-mono:  "JetBrains Mono", "Fira Code", "Cascadia Code", Consolas, monospace;
      --wds-font-size-xs:   0.6875rem;
      --wds-font-size-sm:   0.8125rem;
      --wds-font-size-base: 0.875rem;
      --wds-font-size-md:   1rem;
      --wds-font-size-lg:   1.125rem;
      --wds-font-size-xl:   1.375rem;
      --wds-font-size-2xl:  1.75rem;
      --wds-font-size-3xl:  2.25rem;

      /* Layout / Shell */
      --wks-sidebar-width:         240px;
      --wks-sidebar-collapsed-width: 56px;
      --wks-topbar-height:         52px;
      --wks-transition:            220ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ----------------------------------------------------------
       WDS DARK THEME
       Applied via: <html data-wds-theme="dark">
    ---------------------------------------------------------- */
    [data-wds-theme="dark"] {
      --wds-bg:            var(--wcs-gray-950);
      --wds-bg-subtle:     var(--wcs-gray-900);
      --wds-surface:       var(--wcs-gray-900);
      --wds-surface-raise: var(--wcs-gray-800);
      --wds-border:        var(--wcs-gray-800);
      --wds-border-strong: var(--wcs-gray-700);

      --wds-text:          var(--wcs-gray-50);
      --wds-text-muted:    var(--wcs-gray-400);
      --wds-text-faint:    var(--wcs-gray-600);

      --wds-accent:        var(--wcs-primary-500);
      --wds-accent-hover:  var(--wcs-primary-400);
      --wds-accent-subtle: oklch(0.282 0.091 267.935 / 0.15);
      --wds-accent-text:   var(--wcs-primary-300);

      --wds-success-bg:    oklch(0.266 0.065 152.934 / 0.2);
      --wds-success-text:  var(--wcs-success-400);
      --wds-warning-bg:    oklch(0.279 0.077 45.635 / 0.2);
      --wds-warning-text:  var(--wcs-warning-400);
      --wds-danger-bg:     oklch(0.258 0.092 26.042 / 0.2);
      --wds-danger-text:   var(--wcs-danger-400);
      --wds-info-bg:       oklch(0.282 0.091 267.935 / 0.2);
      --wds-info-text:     var(--wcs-info-400);
    }

    /* ----------------------------------------------------------
       BASE RESET
    ---------------------------------------------------------- */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html { font-size: 16px; }

    body {
      font-family: var(--wds-font-sans);
      font-size: var(--wds-font-size-base);
      background: var(--wds-bg);
      color: var(--wds-text);
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      overflow-x: hidden;
    }

    a { color: inherit; text-decoration: none; }
    button { cursor: pointer; font: inherit; border: none; background: none; }
    svg { display: block; flex-shrink: 0; }

    /* ============================================================
       WKS — SHELL LAYOUT SYSTEM
       Layouts:
         [data-wds-layout="sidebar"]   — classic sidebar + content
         [data-wds-layout="topnav"]    — full top navbar
         [data-wds-layout="horizontal"]— horizontal nav under top bar
    ============================================================ */

    /* --- Shell wrapper ---------------------------------------- */
    .wks-shell {
      display: flex;
      min-height: 100vh;
      position: relative;
    }

    /* ============================================================
       SIDEBAR LAYOUT
    ============================================================ */

    /* --- Sidebar ---------------------------------------------- */
    .wks-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: var(--wks-sidebar-width);
      background: var(--wds-surface);
      border-right: 1px solid var(--wds-border);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: width var(--wks-transition);
      z-index: 50;
    }

    /* Collapsed state */
    [data-wds-sidebar="collapsed"] .wks-sidebar {
      width: var(--wks-sidebar-collapsed-width);
    }

    /* Sidebar inner scrollable zone */
    .wks-sidebar__inner {
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      display: flex;
      flex-direction: column;
      padding: 0.75rem 0;
      scrollbar-width: none;
    }
    .wks-sidebar__inner::-webkit-scrollbar { display: none; }

    /* Brand / logo row */
    .wks-sidebar__brand {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      padding: 0.75rem 1rem;
      height: var(--wks-topbar-height);
      border-bottom: 1px solid var(--wds-border);
      overflow: hidden;
      flex-shrink: 0;
    }

    .wks-brand-icon {
      width: 28px;
      height: 28px;
      border-radius: var(--wds-radius);
      background: var(--wds-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .wks-brand-icon svg { color: #fff; }

    .wks-brand-name {
      font-size: var(--wds-font-size-base);
      font-weight: 600;
      letter-spacing: -0.01em;
      white-space: nowrap;
      opacity: 1;
      transition: opacity var(--wks-transition);
      color: var(--wds-text);
    }

    [data-wds-sidebar="collapsed"] .wks-brand-name,
    [data-wds-sidebar="collapsed"] .wks-nav-label,
    [data-wds-sidebar="collapsed"] .wks-nav-section-title,
    [data-wds-sidebar="collapsed"] .wks-sidebar-footer__text {
      opacity: 0;
      pointer-events: none;
      width: 0;
    }

    /* Nav section label */
    .wks-nav-section-title {
      font-size: var(--wds-font-size-xs);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: var(--wds-text-faint);
      padding: 1rem 1rem 0.375rem;
      white-space: nowrap;
      overflow: hidden;
      transition: opacity var(--wks-transition);
    }

    /* Nav group */
    .wks-nav-group {
      display: flex;
      flex-direction: column;
      gap: 1px;
      padding: 0 0.5rem;
    }

    /* Nav item */
    .wks-nav-item {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      padding: 0.5rem 0.625rem;
      border-radius: var(--wds-radius);
      color: var(--wds-text-muted);
      font-size: var(--wds-font-size-sm);
      font-weight: 500;
      white-space: nowrap;
      transition: background var(--wks-transition), color var(--wks-transition);
      position: relative;
      cursor: pointer;
    }

    .wks-nav-item:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }

    .wks-nav-item.active {
      background: var(--wds-accent-subtle);
      color: var(--wds-accent-text);
    }

    .wks-nav-item .wks-nav-icon {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .wks-nav-label {
      transition: opacity var(--wks-transition);
      white-space: nowrap;
      overflow: hidden;
    }

    /* Badge inside nav item */
    .wks-nav-badge {
      margin-left: auto;
      background: var(--wds-accent);
      color: #fff;
      font-size: 0.625rem;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: var(--wds-radius-full);
      line-height: 1.6;
    }

    /* Sidebar footer (user avatar row) */
    .wks-sidebar-footer {
      border-top: 1px solid var(--wds-border);
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.625rem;
      overflow: hidden;
      flex-shrink: 0;
    }

    .wks-avatar {
      width: 28px;
      height: 28px;
      border-radius: var(--wds-radius-full);
      background: var(--wcs-primary-200);
      color: var(--wcs-primary-800);
      font-size: 0.6875rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .wks-sidebar-footer__text {
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: opacity var(--wks-transition);
    }
    .wks-sidebar-footer__name {
      font-size: var(--wds-font-size-sm);
      font-weight: 600;
      white-space: nowrap;
    }
    .wks-sidebar-footer__role {
      font-size: var(--wds-font-size-xs);
      color: var(--wds-text-muted);
      white-space: nowrap;
    }

    /* ============================================================
       MAIN CONTENT AREA
    ============================================================ */
    .wks-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      margin-left: var(--wks-sidebar-width);
      transition: margin-left var(--wks-transition);
    }

    [data-wds-sidebar="collapsed"] .wks-main {
      margin-left: var(--wks-sidebar-collapsed-width);
    }

    /* Full topnav layout: no sidebar offset */
    [data-wds-layout="topnav"] .wks-main,
    [data-wds-layout="horizontal"] .wks-main {
      margin-left: 0;
    }

    /* ============================================================
       TOP NAVBAR
    ============================================================ */
    .wks-topbar {
      height: var(--wks-topbar-height);
      border-bottom: 1px solid var(--wds-border);
      display: flex;
      align-items: center;
      padding: 0 1.25rem;
      gap: 0.75rem;
      background: var(--wds-surface);
      position: sticky;
      top: 0;
      z-index: 40;
    }

    /* Toggle sidebar button */
    .wks-sidebar-toggle {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--wds-radius);
      color: var(--wds-text-muted);
      transition: background var(--wks-transition), color var(--wks-transition);
      flex-shrink: 0;
    }
    .wks-sidebar-toggle:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }
    .wks-sidebar-toggle svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
    }

    /* Breadcrumb */
    .wks-breadcrumb {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      font-size: var(--wds-font-size-sm);
      color: var(--wds-text-muted);
    }
    .wks-breadcrumb__sep {
      color: var(--wds-border-strong);
      font-size: 0.75rem;
    }
    .wks-breadcrumb__current {
      color: var(--wds-text);
      font-weight: 500;
    }

    /* Topbar right slot */
    .wks-topbar-right {
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    /* Icon button */
    .wks-icon-btn {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--wds-radius);
      border: 1px solid var(--wds-border);
      color: var(--wds-text-muted);
      background: var(--wds-surface);
      transition: background var(--wks-transition), color var(--wks-transition), border-color var(--wks-transition);
    }
    .wks-icon-btn:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
      border-color: var(--wds-border-strong);
    }
    .wks-icon-btn svg {
      width: 15px;
      height: 15px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Layout switcher pill */
    .wks-layout-switcher {
      display: flex;
      align-items: center;
      background: var(--wds-bg-subtle);
      border: 1px solid var(--wds-border);
      border-radius: var(--wds-radius);
      padding: 2px;
      gap: 2px;
    }
    .wks-layout-btn {
      padding: 0.25rem 0.625rem;
      font-size: var(--wds-font-size-xs);
      font-weight: 500;
      border-radius: calc(var(--wds-radius) - 2px);
      color: var(--wds-text-muted);
      transition: background var(--wks-transition), color var(--wks-transition);
      white-space: nowrap;
    }
    .wks-layout-btn:hover {
      color: var(--wds-text);
    }
    .wks-layout-btn.active {
      background: var(--wds-surface);
      color: var(--wds-text);
      box-shadow: var(--wds-shadow-sm);
    }

    /* ============================================================
       HORIZONTAL NAV BAR (sub-topbar for "horizontal" layout)
    ============================================================ */
    .wks-horiz-nav {
      display: none;
      align-items: center;
      gap: 0.25rem;
      height: 42px;
      padding: 0 1.25rem;
      border-bottom: 1px solid var(--wds-border);
      background: var(--wds-surface);
    }

    [data-wds-layout="horizontal"] .wks-horiz-nav {
      display: flex;
    }

    .wks-horiz-nav-item {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.3125rem 0.75rem;
      font-size: var(--wds-font-size-sm);
      font-weight: 500;
      color: var(--wds-text-muted);
      border-radius: var(--wds-radius);
      cursor: pointer;
      transition: background var(--wks-transition), color var(--wks-transition);
    }
    .wks-horiz-nav-item svg {
      width: 14px;
      height: 14px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .wks-horiz-nav-item:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }
    .wks-horiz-nav-item.active {
      background: var(--wds-accent-subtle);
      color: var(--wds-accent-text);
    }

    /* ============================================================
       FULL TOPNAV LAYOUT: sidebar hidden, top brand bar visible
    ============================================================ */
    [data-wds-layout="sidebar"] .wks-sidebar {
      display: flex;
    }

    [data-wds-layout="topnav"] .wks-sidebar,
    [data-wds-layout="horizontal"] .wks-sidebar {
      display: none;
    }

    .wks-topbar-brand {
      display: none;
      align-items: center;
      gap: 0.5rem;
      margin-right: 1rem;
    }

    [data-wds-layout="topnav"] .wks-topbar-brand,
    [data-wds-layout="horizontal"] .wks-topbar-brand {
      display: flex;
    }

    .wks-topbar-nav {
      display: none;
      align-items: center;
      gap: 0.25rem;
    }

    [data-wds-layout="topnav"] .wks-topbar-nav {
      display: flex;
    }

    .wks-topnav-item {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.3125rem 0.75rem;
      font-size: var(--wds-font-size-sm);
      font-weight: 500;
      color: var(--wds-text-muted);
      border-radius: var(--wds-radius);
      cursor: pointer;
      transition: background var(--wks-transition), color var(--wks-transition);
    }
    .wks-topnav-item:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }
    .wks-topnav-item.active {
      background: var(--wds-accent-subtle);
      color: var(--wds-accent-text);
    }

    /* Toggle sidebar icon: hidden in topnav / horizontal modes */
    [data-wds-layout="topnav"] .wks-sidebar-toggle,
    [data-wds-layout="horizontal"] .wks-sidebar-toggle {
      display: none;
    }

    [data-wds-layout="topnav"] .wks-breadcrumb,
    [data-wds-layout="horizontal"] .wks-breadcrumb {
      display: none;
    }

    /* ============================================================
       PAGE CONTENT WRAPPER
    ============================================================ */
    .wks-content {
      flex: 1;
      padding: 1.75rem 2rem;
      max-width: 1280px;
      width: 100%;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 1.75rem;
    }

    /* Page header */
    .wks-page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
    }
    .wks-page-title {
      font-size: var(--wds-font-size-2xl);
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.2;
    }
    .wks-page-desc {
      margin-top: 0.25rem;
      font-size: var(--wds-font-size-sm);
      color: var(--wds-text-muted);
    }
    .wks-page-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-shrink: 0;
    }

    /* ============================================================
       WDS CARD COMPONENT
    ============================================================ */
    .wds-card {
      background: var(--wds-surface);
      border: 1px solid var(--wds-border);
      border-radius: var(--wds-radius-lg);
      box-shadow: var(--wds-shadow-sm);
      overflow: hidden;
    }
    .wds-card-header {
      padding: 1.25rem 1.5rem 0;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
    }
    .wds-card-title {
      font-size: var(--wds-font-size-base);
      font-weight: 600;
      letter-spacing: -0.01em;
    }
    .wds-card-desc {
      font-size: var(--wds-font-size-sm);
      color: var(--wds-text-muted);
      margin-top: 0.125rem;
    }
    .wds-card-body {
      padding: 1.25rem 1.5rem 1.5rem;
    }

    /* ============================================================
       KPI METRIC CARDS
    ============================================================ */
    .wks-metrics-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }

    .wks-metric-card {
      background: var(--wds-surface);
      border: 1px solid var(--wds-border);
      border-radius: var(--wds-radius-lg);
      padding: 1.25rem 1.5rem;
      box-shadow: var(--wds-shadow-sm);
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .wks-metric-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .wks-metric-label {
      font-size: var(--wds-font-size-sm);
      font-weight: 500;
      color: var(--wds-text-muted);
    }
    .wks-metric-icon {
      width: 16px;
      height: 16px;
      stroke: var(--wds-text-muted);
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .wks-metric-value {
      font-size: var(--wds-font-size-2xl);
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.1;
    }
    .wks-metric-trend {
      font-size: var(--wds-font-size-xs);
      color: var(--wds-text-muted);
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .wks-metric-trend.up   { color: var(--wds-success-text); }
    .wks-metric-trend.down { color: var(--wds-danger-text); }
    .wks-metric-trend svg {
      width: 12px;
      height: 12px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2.5;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* ============================================================
       BENTO GRID (analytics section)
    ============================================================ */
    .wks-bento {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    .wks-bento--wide { grid-template-columns: 3fr 2fr; }

    /* ============================================================
       BAR CHART (pure CSS)
    ============================================================ */
    .wds-chart {
      display: flex;
      align-items: flex-end;
      gap: 6px;
      height: 160px;
      padding-top: 1rem;
      margin-top: 0.75rem;
    }
    .wds-chart__col {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.375rem;
      height: 100%;
      justify-content: flex-end;
    }
    .wds-chart__bar {
      width: 100%;
      background: var(--wds-accent);
      border-radius: var(--wds-radius-sm) var(--wds-radius-sm) 0 0;
      opacity: 0.85;
      transition: opacity 0.15s;
      min-height: 4px;
    }
    .wds-chart__bar:hover { opacity: 1; }
    .wds-chart__bar--secondary {
      background: var(--wcs-primary-200);
    }
    [data-wds-theme="dark"] .wds-chart__bar--secondary {
      background: var(--wcs-primary-800);
    }
    .wds-chart__lbl {
      font-size: var(--wds-font-size-xs);
      color: var(--wds-text-faint);
      white-space: nowrap;
    }

    /* Mini sparkline */
    .wds-sparkline {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 36px;
    }
    .wds-sparkline__bar {
      flex: 1;
      background: var(--wds-accent);
      border-radius: 2px 2px 0 0;
      opacity: 0.6;
    }

    /* ============================================================
       TABLE
    ============================================================ */
    .wds-table-wrap {
      overflow-x: auto;
      margin-top: 0.75rem;
    }
    .wds-table {
      width: 100%;
      border-collapse: collapse;
      font-size: var(--wds-font-size-sm);
      text-align: left;
    }
    .wds-table thead th {
      padding: 0.625rem 0.75rem;
      font-weight: 500;
      color: var(--wds-text-muted);
      border-bottom: 1px solid var(--wds-border);
      white-space: nowrap;
    }
    .wds-table tbody tr {
      transition: background var(--wks-transition);
    }
    .wds-table tbody tr:hover {
      background: var(--wds-bg-subtle);
    }
    .wds-table tbody td {
      padding: 0.625rem 0.75rem;
      border-bottom: 1px solid var(--wds-border);
    }
    .wds-table tbody tr:last-child td {
      border-bottom: none;
    }

    /* ============================================================
       BADGE COMPONENT
    ============================================================ */
    .wds-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.125rem 0.5rem;
      border-radius: var(--wds-radius-full);
      font-size: var(--wds-font-size-xs);
      font-weight: 600;
      border: 1px solid transparent;
      white-space: nowrap;
    }
    .wds-badge::before {
      content: '';
      display: block;
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: currentColor;
    }
    .wds-badge--success {
      background: var(--wds-success-bg);
      color: var(--wds-success-text);
    }
    .wds-badge--warning {
      background: var(--wds-warning-bg);
      color: var(--wds-warning-text);
    }
    .wds-badge--danger {
      background: var(--wds-danger-bg);
      color: var(--wds-danger-text);
    }
    .wds-badge--info {
      background: var(--wds-info-bg);
      color: var(--wds-info-text);
    }
    .wds-badge--neutral {
      background: var(--wds-bg-subtle);
      color: var(--wds-text-muted);
    }

    /* ============================================================
       BUTTON COMPONENT
    ============================================================ */
    .wds-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.4375rem 0.875rem;
      font-size: var(--wds-font-size-sm);
      font-weight: 500;
      border-radius: var(--wds-radius);
      border: 1px solid transparent;
      cursor: pointer;
      transition: background var(--wks-transition), color var(--wks-transition), border-color var(--wks-transition), box-shadow var(--wks-transition);
      white-space: nowrap;
      line-height: 1.4;
    }
    .wds-btn svg {
      width: 14px;
      height: 14px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .wds-btn--primary {
      background: var(--wds-accent);
      color: #fff;
    }
    .wds-btn--primary:hover {
      background: var(--wds-accent-hover);
    }
    .wds-btn--outline {
      background: var(--wds-surface);
      border-color: var(--wds-border);
      color: var(--wds-text);
    }
    .wds-btn--outline:hover {
      background: var(--wds-bg-subtle);
      border-color: var(--wds-border-strong);
    }
    .wds-btn--ghost {
      background: transparent;
      color: var(--wds-text-muted);
    }
    .wds-btn--ghost:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }
    .wds-btn--sm {
      padding: 0.25rem 0.625rem;
      font-size: var(--wds-font-size-xs);
    }

    /* ============================================================
       ACTIVITY / RECENT LIST
    ============================================================ */
    .wks-activity-list {
      display: flex;
      flex-direction: column;
      gap: 0;
      margin-top: 0.75rem;
    }
    .wks-activity-item {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--wds-border);
    }
    .wks-activity-item:last-child { border-bottom: none; }
    .wks-activity-dot {
      width: 28px;
      height: 28px;
      border-radius: var(--wds-radius-full);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 0.625rem;
      font-weight: 700;
    }
    .wks-activity-dot--success { background: var(--wds-success-bg); color: var(--wds-success-text); }
    .wks-activity-dot--warning { background: var(--wds-warning-bg); color: var(--wds-warning-text); }
    .wks-activity-dot--info    { background: var(--wds-info-bg);    color: var(--wds-info-text); }
    .wks-activity-dot--danger  { background: var(--wds-danger-bg);  color: var(--wds-danger-text); }

    .wks-activity-body { flex: 1; min-width: 0; }
    .wks-activity-title {
      font-size: var(--wds-font-size-sm);
      font-weight: 500;
    }
    .wks-activity-meta {
      font-size: var(--wds-font-size-xs);
      color: var(--wds-text-muted);
      margin-top: 0.125rem;
    }
    .wks-activity-time {
      font-size: var(--wds-font-size-xs);
      color: var(--wds-text-faint);
      flex-shrink: 0;
    }

    /* ============================================================
       PROGRESS BAR
    ============================================================ */
    .wds-progress {
      height: 6px;
      border-radius: var(--wds-radius-full);
      background: var(--wds-bg-subtle);
      overflow: hidden;
    }
    .wds-progress__bar {
      height: 100%;
      border-radius: var(--wds-radius-full);
      background: var(--wds-accent);
      transition: width 0.4s ease;
    }

    /* ============================================================
       TAB BAR
    ============================================================ */
    .wds-tabs {
      display: flex;
      gap: 0;
      border-bottom: 1px solid var(--wds-border);
      margin-bottom: 1rem;
    }
    .wds-tab {
      padding: 0.5rem 1rem;
      font-size: var(--wds-font-size-sm);
      font-weight: 500;
      color: var(--wds-text-muted);
      border-bottom: 2px solid transparent;
      margin-bottom: -1px;
      cursor: pointer;
      transition: color var(--wks-transition), border-color var(--wks-transition);
    }
    .wds-tab:hover { color: var(--wds-text); }
    .wds-tab.active {
      color: var(--wds-accent-text);
      border-bottom-color: var(--wds-accent);
    }

    /* ============================================================
       STAT DONUT (CSS only)
    ============================================================ */
    .wds-donut-wrap {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      margin-top: 0.75rem;
    }
    .wds-donut {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: conic-gradient(var(--wds-accent) 0% 72%, var(--wds-bg-subtle) 72% 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      flex-shrink: 0;
    }
    .wds-donut::after {
      content: '';
      position: absolute;
      width: 54px;
      height: 54px;
      border-radius: 50%;
      background: var(--wds-surface);
    }
    .wds-donut__label {
      position: relative;
      z-index: 1;
      font-size: var(--wds-font-size-sm);
      font-weight: 700;
    }
    .wds-donut-legend { display: flex; flex-direction: column; gap: 0.5rem; }
    .wds-legend-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: var(--wds-font-size-xs);
    }
    .wds-legend-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* ============================================================
       RESPONSIVE
    ============================================================ */
    @media (max-width: 1100px) {
      .wks-metrics-grid { grid-template-columns: repeat(2, 1fr); }
      .wks-bento--wide  { grid-template-columns: 1fr; }
    }
    @media (max-width: 860px) {
      .wks-bento { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      .wks-metrics-grid { grid-template-columns: 1fr 1fr; }
      .wks-content { padding: 1rem; gap: 1rem; }
      .wks-page-header { flex-direction: column; }
      .wks-layout-switcher { display: none; }
    }

    /* ============================================================
       UTILITY
    ============================================================ */
    .u-flex-gap-sm { display: flex; align-items: center; gap: 0.375rem; }
    .u-ml-auto { margin-left: auto; }
    .u-text-muted { color: var(--wds-text-muted); }
    .u-text-sm { font-size: var(--wds-font-size-sm); }
    .u-font-mono { font-family: var(--wds-font-mono); }
  </style>
</head>
<body>

<!-- ================================================================
     WEBKERNEL SHELL
     JS drives:
       html[data-wds-theme]   = "light" | "dark"
       html[data-wds-layout]  = "sidebar" | "topnav" | "horizontal"
       html[data-wds-sidebar] = "expanded" | "collapsed"
================================================================ -->
<div class="wks-shell">

  <!-- ==============================================================
       SIDEBAR
       Visible only when data-wds-layout="sidebar"
  ============================================================== -->
  <aside class="wks-sidebar" id="sidebar" role="navigation" aria-label="Main navigation">

    <!-- Brand -->
    <div class="wks-sidebar__brand">
      <div class="wks-brand-icon">
        <!-- Webkernel logo mark -->
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="8" cy="8" r="6"/>
          <path d="M8 2v6l3 3"/>
        </svg>
      </div>
      <span class="wks-brand-name">Webkernel</span>
    </div>

    <!-- Scrollable nav -->
    <div class="wks-sidebar__inner">

      <!-- Main nav group -->
      <span class="wks-nav-section-title">Platform</span>
      <nav class="wks-nav-group">
        <a href="#" class="wks-nav-item active" onclick="setPage(event,'overview')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
          <span class="wks-nav-label">Overview</span>
        </a>
        <a href="#" class="wks-nav-item" onclick="setPage(event,'analytics')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><polyline points="1 12 5 7 9 9 15 3"/><line x1="1" y1="15" x2="15" y2="15"/></svg>
          <span class="wks-nav-label">Analytics</span>
        </a>
        <a href="#" class="wks-nav-item" onclick="setPage(event,'orders')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><rect x="1" y="4" width="14" height="10" rx="1"/><path d="M5 4V3a2 2 0 0 1 6 0v1"/></svg>
          <span class="wks-nav-label">Orders</span>
          <span class="wks-nav-badge">12</span>
        </a>
        <a href="#" class="wks-nav-item" onclick="setPage(event,'reports')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><path d="M9 1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="9 1 9 6 14 6"/></svg>
          <span class="wks-nav-label">Reports</span>
        </a>
        <a href="#" class="wks-nav-item" onclick="setPage(event,'calendar')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="13" rx="1"/><line x1="1" y1="6" x2="15" y2="6"/><line x1="5" y1="1" x2="5" y2="3"/><line x1="11" y1="1" x2="11" y2="3"/></svg>
          <span class="wks-nav-label">Calendar</span>
        </a>
      </nav>

      <!-- Settings group -->
      <span class="wks-nav-section-title">Manage</span>
      <nav class="wks-nav-group">
        <a href="#" class="wks-nav-item" onclick="setPage(event,'notifications')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><path d="M8 1a5 5 0 0 1 5 5v3l1.5 2H1.5L3 9V6a5 5 0 0 1 5-5z"/><path d="M6.5 14a1.5 1.5 0 0 0 3 0"/></svg>
          <span class="wks-nav-label">Notifications</span>
          <span class="wks-nav-badge">3</span>
        </a>
        <a href="#" class="wks-nav-item" onclick="setPage(event,'team')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><circle cx="6" cy="5" r="2.5"/><path d="M1 13c0-2.8 2.2-5 5-5s5 2.2 5 5"/><circle cx="12" cy="5" r="2"/><path d="M12 9c1.7 0 3 1.3 3 3"/></svg>
          <span class="wks-nav-label">Team</span>
        </a>
        <a href="#" class="wks-nav-item" onclick="setPage(event,'settings')">
          <svg class="wks-nav-icon" viewBox="0 0 16 16"><circle cx="8" cy="8" r="2"/><path d="M8 1v2m0 10v2M1 8h2m10 0h2m-3-5L11.5 4.5M4.5 11.5 3 13m11 0-1.5-1.5M3 3l1.5 1.5"/></svg>
          <span class="wks-nav-label">Settings</span>
        </a>
      </nav>
    </div><!-- /.wks-sidebar__inner -->

    <!-- User footer -->
    <div class="wks-sidebar-footer">
      <div class="wks-avatar">JD</div>
      <div class="wks-sidebar-footer__text">
        <span class="wks-sidebar-footer__name">Jane Doe</span>
        <span class="wks-sidebar-footer__role">Admin</span>
      </div>
    </div>

  </aside>

  <!-- ==============================================================
       MAIN CONTENT
  ============================================================== -->
  <div class="wks-main">

    <!-- TOP BAR -->
    <header class="wks-topbar">

      <!-- Sidebar toggle (sidebar layout only) -->
      <button class="wks-sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
        <svg viewBox="0 0 16 16"><line x1="1" y1="4" x2="15" y2="4"/><line x1="1" y1="8" x2="15" y2="8"/><line x1="1" y1="12" x2="15" y2="12"/></svg>
      </button>

      <!-- Brand (topnav / horizontal layouts) -->
      <div class="wks-topbar-brand">
        <div class="wks-brand-icon" style="width:24px;height:24px;">
          <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="8" r="6"/><path d="M8 2v6l3 3"/>
          </svg>
        </div>
        <span style="font-size:var(--wds-font-size-base);font-weight:600;letter-spacing:-0.01em;">Webkernel</span>
      </div>

      <!-- Inline topnav (topnav layout only) -->
      <nav class="wks-topbar-nav">
        <a class="wks-topnav-item active">Overview</a>
        <a class="wks-topnav-item">Analytics</a>
        <a class="wks-topnav-item">Orders</a>
        <a class="wks-topnav-item">Reports</a>
        <a class="wks-topnav-item">Settings</a>
      </nav>

      <!-- Breadcrumb (sidebar layout) -->
      <div class="wks-breadcrumb" id="breadcrumb">
        <span>Dashboard</span>
        <span class="wks-breadcrumb__sep">/</span>
        <span class="wks-breadcrumb__current" id="breadcrumb-current">Overview</span>
      </div>

      <!-- Right-side actions -->
      <div class="wks-topbar-right">

        <!-- Layout switcher -->
        <div class="wks-layout-switcher" title="Switch layout">
          <button class="wks-layout-btn active" id="btn-layout-sidebar"     onclick="setLayout('sidebar')">Sidebar</button>
          <button class="wks-layout-btn"         id="btn-layout-topnav"      onclick="setLayout('topnav')">Top Nav</button>
          <button class="wks-layout-btn"         id="btn-layout-horizontal"  onclick="setLayout('horizontal')">Horizontal</button>
        </div>

        <!-- Search -->
        <button class="wks-icon-btn" title="Search">
          <svg viewBox="0 0 16 16"><circle cx="6.5" cy="6.5" r="4.5"/><line x1="10" y1="10" x2="14" y2="14"/></svg>
        </button>

        <!-- Notifications -->
        <button class="wks-icon-btn" title="Notifications">
          <svg viewBox="0 0 16 16"><path d="M8 1a5 5 0 0 1 5 5v3l1.5 2H1.5L3 9V6a5 5 0 0 1 5-5z"/><path d="M6.5 14a1.5 1.5 0 0 0 3 0"/></svg>
        </button>

        <!-- Dark mode -->
        <button class="wks-icon-btn" onclick="toggleTheme()" title="Toggle theme" id="theme-btn">
          <svg id="icon-sun" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3"/><line x1="8" y1="1" x2="8" y2="3"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="1" y1="8" x2="3" y2="8"/><line x1="13" y1="8" x2="15" y2="8"/><line x1="3.2" y1="3.2" x2="4.6" y2="4.6"/><line x1="11.4" y1="11.4" x2="12.8" y2="12.8"/><line x1="12.8" y1="3.2" x2="11.4" y2="4.6"/><line x1="4.6" y1="11.4" x2="3.2" y2="12.8"/></svg>
          <svg id="icon-moon" viewBox="0 0 16 16" style="display:none;"><path d="M12 10A6 6 0 0 1 6 4a6.003 6.003 0 0 0 6 9 6 6 0 0 1 0-3z"/></svg>
        </button>

        <!-- User avatar -->
        <div class="wks-avatar" style="cursor:pointer;" title="Jane Doe — Admin">JD</div>

      </div>
    </header>

    <!-- Horizontal sub-nav (horizontal layout only) -->
    <div class="wks-horiz-nav">
      <a class="wks-horiz-nav-item active">
        <svg viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
        Overview
      </a>
      <a class="wks-horiz-nav-item">
        <svg viewBox="0 0 16 16"><polyline points="1 12 5 7 9 9 15 3"/><line x1="1" y1="15" x2="15" y2="15"/></svg>
        Analytics
      </a>
      <a class="wks-horiz-nav-item">
        <svg viewBox="0 0 16 16"><rect x="1" y="4" width="14" height="10" rx="1"/><path d="M5 4V3a2 2 0 0 1 6 0v1"/></svg>
        Orders
      </a>
      <a class="wks-horiz-nav-item">
        <svg viewBox="0 0 16 16"><path d="M9 1H3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="9 1 9 6 14 6"/></svg>
        Reports
      </a>
      <a class="wks-horiz-nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="2"/><path d="M8 1v2m0 10v2M1 8h2m10 0h2m-3-5L11.5 4.5M4.5 11.5 3 13m11 0-1.5-1.5M3 3l1.5 1.5"/></svg>
        Settings
      </a>
    </div>

    <!-- ============================================================
         PAGE CONTENT
    ============================================================ -->
    <main class="wks-content">

      <!-- Page header -->
      <div class="wks-page-header">
        <div>
          <h1 class="wks-page-title">Dashboard Overview</h1>
          <p class="wks-page-desc">Real-time platform metrics and event streams. &nbsp;<span style="font-family:var(--wds-font-mono);font-size:var(--wds-font-size-xs);color:var(--wds-text-faint);">rendered in <?php echo number_format((hrtime(true) - START_REQUEST) / 1e6, 2) . " ms"; ?></span>

          </p>
        </div>
        <div class="wks-page-actions">
          <button class="wds-btn wds-btn--outline wds-btn--sm">
            <svg viewBox="0 0 16 16"><path d="M2 8a6 6 0 1 0 12 0A6 6 0 0 0 2 8z"/><line x1="8" y1="5" x2="8" y2="8"/><line x1="8" y1="10" x2="8.01" y2="10"/></svg>
            Export
          </button>
          <button class="wds-btn wds-btn--primary wds-btn--sm">
            <svg viewBox="0 0 16 16"><line x1="8" y1="1" x2="8" y2="15"/><line x1="1" y1="8" x2="15" y2="8"/></svg>
            New Report
          </button>
        </div>
      </div>

      <!-- KPI cards -->
      <section class="wks-metrics-grid">

        <div class="wks-metric-card">
          <div class="wks-metric-header">
            <span class="wks-metric-label">Total Revenue</span>
            <svg class="wks-metric-icon" viewBox="0 0 16 16"><line x1="1" y1="11" x2="5" y2="7"/><line x1="5" y1="7" x2="9" y2="9"/><line x1="9" y1="9" x2="15" y2="2"/></svg>
          </div>
          <div class="wks-metric-value">$45,231</div>
          <div class="wks-metric-trend up">
            <svg viewBox="0 0 12 12"><polyline points="1 8 4 4 7 6 11 1"/></svg>
            +20.1% vs last month
          </div>
          <div class="wds-sparkline" style="margin-top:0.5rem;">
            <div class="wds-sparkline__bar" style="height:40%"></div>
            <div class="wds-sparkline__bar" style="height:55%"></div>
            <div class="wds-sparkline__bar" style="height:35%"></div>
            <div class="wds-sparkline__bar" style="height:65%"></div>
            <div class="wds-sparkline__bar" style="height:50%"></div>
            <div class="wds-sparkline__bar" style="height:80%"></div>
            <div class="wds-sparkline__bar" style="height:72%"></div>
            <div class="wds-sparkline__bar" style="height:100%"></div>
          </div>
        </div>

        <div class="wks-metric-card">
          <div class="wks-metric-header">
            <span class="wks-metric-label">Subscriptions</span>
            <svg class="wks-metric-icon" viewBox="0 0 16 16"><path d="M14 7H9V2H7v5H2v2h5v5h2V9h5z"/></svg>
          </div>
          <div class="wks-metric-value">+2,350</div>
          <div class="wks-metric-trend up">
            <svg viewBox="0 0 12 12"><polyline points="1 8 4 4 7 6 11 1"/></svg>
            +180.1% vs last month
          </div>
          <div class="wds-progress" style="margin-top:0.75rem;">
            <div class="wds-progress__bar" style="width:78%;background:var(--wcs-success-500);"></div>
          </div>
          <div class="wks-metric-trend u-text-muted" style="margin-top:0.375rem;">78% of monthly target</div>
        </div>

        <div class="wks-metric-card">
          <div class="wks-metric-header">
            <span class="wks-metric-label">Sales</span>
            <svg class="wks-metric-icon" viewBox="0 0 16 16"><circle cx="6" cy="13" r="1.5"/><circle cx="12" cy="13" r="1.5"/><path d="M1 1h2l2 8h7l1.5-5H5"/></svg>
          </div>
          <div class="wks-metric-value">12,234</div>
          <div class="wks-metric-trend up">
            <svg viewBox="0 0 12 12"><polyline points="1 8 4 4 7 6 11 1"/></svg>
            +19% vs last month
          </div>
          <div class="wds-progress" style="margin-top:0.75rem;">
            <div class="wds-progress__bar" style="width:62%;background:var(--wcs-warning-500);"></div>
          </div>
          <div class="wks-metric-trend u-text-muted" style="margin-top:0.375rem;">62% of quarterly goal</div>
        </div>

        <div class="wks-metric-card">
          <div class="wks-metric-header">
            <span class="wks-metric-label">Active Now</span>
            <svg class="wks-metric-icon" viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><polyline points="8 5 8 8 10 10"/></svg>
          </div>
          <div class="wks-metric-value">+573</div>
          <div class="wks-metric-trend up">
            <svg viewBox="0 0 12 12"><polyline points="1 8 4 4 7 6 11 1"/></svg>
            +201 since last hour
          </div>
          <div style="display:flex;align-items:center;gap:0.375rem;margin-top:0.625rem;">
            <span style="width:7px;height:7px;border-radius:50%;background:var(--wcs-success-500);display:inline-block;box-shadow:0 0 0 3px var(--wds-success-bg);"></span>
            <span style="font-size:var(--wds-font-size-xs);color:var(--wds-success-text);font-weight:500;">Live tracking</span>
          </div>
        </div>

      </section>

      <!-- Bento analytics grid -->
      <div class="wks-bento wks-bento--wide">

        <!-- Bar chart card -->
        <div class="wds-card">
          <div class="wds-card-header">
            <div>
              <div class="wds-card-title">Revenue Overview</div>
              <div class="wds-card-desc">Monthly performance for the current year</div>
            </div>
            <div class="u-flex-gap-sm">
              <div style="display:flex;align-items:center;gap:0.3rem;font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">
                <span style="width:8px;height:8px;border-radius:2px;background:var(--wds-accent);display:block;"></span>Revenue
              </div>
              <div style="display:flex;align-items:center;gap:0.3rem;font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">
                <span style="width:8px;height:8px;border-radius:2px;background:var(--wcs-primary-200);display:block;"></span>Target
              </div>
            </div>
          </div>
          <div class="wds-card-body">
            <div class="wds-chart">
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:60%;"></div>
                <div class="wds-chart__bar" style="height:35%;"></div>
                <span class="wds-chart__lbl">Jan</span>
              </div>
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:70%;"></div>
                <div class="wds-chart__bar" style="height:55%;"></div>
                <span class="wds-chart__lbl">Feb</span>
              </div>
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:45%;"></div>
                <div class="wds-chart__bar" style="height:20%;"></div>
                <span class="wds-chart__lbl">Mar</span>
              </div>
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:80%;"></div>
                <div class="wds-chart__bar" style="height:65%;"></div>
                <span class="wds-chart__lbl">Apr</span>
              </div>
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:60%;"></div>
                <div class="wds-chart__bar" style="height:48%;"></div>
                <span class="wds-chart__lbl">May</span>
              </div>
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:90%;"></div>
                <div class="wds-chart__bar" style="height:85%;"></div>
                <span class="wds-chart__lbl">Jun</span>
              </div>
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:85%;"></div>
                <div class="wds-chart__bar" style="height:72%;"></div>
                <span class="wds-chart__lbl">Jul</span>
              </div>
              <div class="wds-chart__col">
                <div class="wds-chart__bar wds-chart__bar--secondary" style="height:100%;"></div>
                <div class="wds-chart__bar" style="height:90%;"></div>
                <span class="wds-chart__lbl">Aug</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right column: donut + activity -->
        <div style="display:flex;flex-direction:column;gap:1rem;">

          <!-- Donut card -->
          <div class="wds-card">
            <div class="wds-card-header">
              <div>
                <div class="wds-card-title">Channel Split</div>
                <div class="wds-card-desc">Revenue by source</div>
              </div>
            </div>
            <div class="wds-card-body">
              <div class="wds-donut-wrap">
                <div class="wds-donut">
                  <span class="wds-donut__label">72%</span>
                </div>
                <div class="wds-donut-legend">
                  <div class="wds-legend-row">
                    <div class="wds-legend-dot" style="background:var(--wds-accent);"></div>
                    <span>Direct — 72%</span>
                  </div>
                  <div class="wds-legend-row">
                    <div class="wds-legend-dot" style="background:var(--wds-bg-subtle);border:1.5px solid var(--wds-border-strong);"></div>
                    <span>Organic — 28%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mini stat -->
          <div class="wds-card">
            <div class="wds-card-body" style="display:flex;flex-direction:column;gap:0.75rem;">
              <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:var(--wds-font-size-sm);font-weight:500;">Conversion Rate</span>
                <span class="wds-badge wds-badge--success">+2.4%</span>
              </div>
              <div style="font-size:var(--wds-font-size-xl);font-weight:700;letter-spacing:-0.02em;">3.68%</div>
              <div class="wds-progress">
                <div class="wds-progress__bar" style="width:36.8%;"></div>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <span style="font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">0%</span>
                <span style="font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">10%</span>
              </div>
            </div>
          </div>

        </div>
      </div><!-- /.wks-bento -->

      <!-- Bottom row: table + activity -->
      <div class="wks-bento">

        <!-- Transactions table -->
        <div class="wds-card">
          <div class="wds-card-header">
            <div>
              <div class="wds-card-title">Recent Transactions</div>
              <div class="wds-card-desc">Latest 5 invoice events</div>
            </div>
            <button class="wds-btn wds-btn--ghost wds-btn--sm">View all</button>
          </div>
          <div class="wds-card-body" style="padding-top:0.5rem;">

            <!-- Tab bar -->
            <div class="wds-tabs">
              <span class="wds-tab active">All</span>
              <span class="wds-tab">Paid</span>
              <span class="wds-tab">Pending</span>
              <span class="wds-tab">Failed</span>
            </div>

            <div class="wds-table-wrap">
              <table class="wds-table">
                <thead>
                  <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="u-font-mono" style="font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">INV-001</td>
                    <td>Acme Corp</td>
                    <td><span class="wds-badge wds-badge--success">Paid</span></td>
                    <td style="font-weight:600;">$1,250.00</td>
                    <td class="u-text-muted">Aug 18</td>
                  </tr>
                  <tr>
                    <td class="u-font-mono" style="font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">INV-002</td>
                    <td>Stark Industries</td>
                    <td><span class="wds-badge wds-badge--warning">Pending</span></td>
                    <td style="font-weight:600;">$320.00</td>
                    <td class="u-text-muted">Aug 17</td>
                  </tr>
                  <tr>
                    <td class="u-font-mono" style="font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">INV-003</td>
                    <td>Globex Corp</td>
                    <td><span class="wds-badge wds-badge--success">Paid</span></td>
                    <td style="font-weight:600;">$850.00</td>
                    <td class="u-text-muted">Aug 16</td>
                  </tr>
                  <tr>
                    <td class="u-font-mono" style="font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">INV-004</td>
                    <td>Initech</td>
                    <td><span class="wds-badge wds-badge--danger">Failed</span></td>
                    <td style="font-weight:600;">$90.00</td>
                    <td class="u-text-muted">Aug 15</td>
                  </tr>
                  <tr>
                    <td class="u-font-mono" style="font-size:var(--wds-font-size-xs);color:var(--wds-text-muted);">INV-005</td>
                    <td>Umbrella LLC</td>
                    <td><span class="wds-badge wds-badge--info">Refunded</span></td>
                    <td style="font-weight:600;">$450.00</td>
                    <td class="u-text-muted">Aug 14</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Recent activity -->
        <div class="wds-card">
          <div class="wds-card-header">
            <div>
              <div class="wds-card-title">Activity Feed</div>
              <div class="wds-card-desc">Platform events in real time</div>
            </div>
          </div>
          <div class="wds-card-body" style="padding-top:0;">
            <div class="wks-activity-list">

              <div class="wks-activity-item">
                <div class="wks-activity-dot wks-activity-dot--success">✓</div>
                <div class="wks-activity-body">
                  <div class="wks-activity-title">New subscription activated</div>
                  <div class="wks-activity-meta">jane@acmecorp.com — Pro plan</div>
                </div>
                <span class="wks-activity-time">2m ago</span>
              </div>

              <div class="wks-activity-item">
                <div class="wks-activity-dot wks-activity-dot--warning">!</div>
                <div class="wks-activity-body">
                  <div class="wks-activity-title">Payment retry scheduled</div>
                  <div class="wks-activity-meta">INV-004 — Initech</div>
                </div>
                <span class="wks-activity-time">14m ago</span>
              </div>

              <div class="wks-activity-item">
                <div class="wks-activity-dot wks-activity-dot--info">i</div>
                <div class="wks-activity-body">
                  <div class="wks-activity-title">Deploy completed</div>
                  <div class="wks-activity-meta">webkernel v2.4.1 — production</div>
                </div>
                <span class="wks-activity-time">1h ago</span>
              </div>

              <div class="wks-activity-item">
                <div class="wks-activity-dot wks-activity-dot--danger">✕</div>
                <div class="wks-activity-body">
                  <div class="wks-activity-title">Webhook delivery failed</div>
                  <div class="wks-activity-meta">endpoint /api/hooks/stripe · 503</div>
                </div>
                <span class="wks-activity-time">2h ago</span>
              </div>

              <div class="wks-activity-item">
                <div class="wks-activity-dot wks-activity-dot--success">✓</div>
                <div class="wks-activity-body">
                  <div class="wks-activity-title">Report exported</div>
                  <div class="wks-activity-meta">Q2 2026 Revenue — PDF</div>
                </div>
                <span class="wks-activity-time">3h ago</span>
              </div>

            </div>
          </div>
        </div>

      </div><!-- /.wks-bento -->

    </main>
  </div><!-- /.wks-main -->
</div><!-- /.wks-shell -->

<!-- ================================================================
     WEBKERNEL SDUI SHELL CONTROLLER
     Drives layout, sidebar, and theme without any framework.
     Each function maps 1-to-1 to an HTML attribute:
       wds-theme | wds-layout | wds-sidebar
================================================================ -->
<script>
  // ── Theme ──────────────────────────────────────────────────────
  function toggleTheme() {
    const html = document.documentElement;
    const next = html.dataset.wdsTheme === 'dark' ? 'light' : 'dark';
    html.dataset.wdsTheme = next;
    document.getElementById('icon-sun').style.display  = next === 'dark' ? 'none'  : 'block';
    document.getElementById('icon-moon').style.display = next === 'dark' ? 'block' : 'none';
    localStorage.setItem('wds-theme', next);
  }

  // ── Sidebar toggle ─────────────────────────────────────────────
  function toggleSidebar() {
    const html = document.documentElement;
    html.dataset.wdsSidebar = html.dataset.wdsSidebar === 'collapsed' ? 'expanded' : 'collapsed';
    localStorage.setItem('wds-sidebar', html.dataset.wdsSidebar);
  }

  // ── Layout switch ──────────────────────────────────────────────
  function setLayout(layout) {
    document.documentElement.dataset.wdsLayout = layout;
    // Sync button states
    ['sidebar', 'topnav', 'horizontal'].forEach(id => {
      const btn = document.getElementById('btn-layout-' + id);
      if (btn) btn.classList.toggle('active', id === layout);
    });
    // In non-sidebar layouts, always expand sidebar state data attr (no-op visually)
    if (layout !== 'sidebar') {
      document.documentElement.dataset.wdsSidebar = 'expanded';
    }
    localStorage.setItem('wds-layout', layout);
  }

  // ── Nav page selection ─────────────────────────────────────────
  function setPage(e, page) {
    e.preventDefault();
    // Update active nav item
    document.querySelectorAll('.wks-nav-item').forEach(el => el.classList.remove('active'));
    e.currentTarget.classList.add('active');
    // Update breadcrumb
    const crumb = document.getElementById('breadcrumb-current');
    if (crumb) crumb.textContent = e.currentTarget.querySelector('.wks-nav-label')?.textContent ?? page;
  }

  // ── Restore persisted state ────────────────────────────────────
  (function restoreState() {
    const savedTheme   = localStorage.getItem('wds-theme');
    const savedLayout  = localStorage.getItem('wds-layout');
    const savedSidebar = localStorage.getItem('wds-sidebar');

    if (savedTheme) {
      document.documentElement.dataset.wdsTheme = savedTheme;
      if (savedTheme === 'dark') {
        document.getElementById('icon-sun').style.display  = 'none';
        document.getElementById('icon-moon').style.display = 'block';
      }
    }
    if (savedLayout) setLayout(savedLayout);
    if (savedSidebar) document.documentElement.dataset.wdsSidebar = savedSidebar;
  })();

  // ── Tab bar interaction ────────────────────────────────────────
  document.querySelectorAll('.wds-tabs').forEach(tabs => {
    tabs.querySelectorAll('.wds-tab').forEach(tab => {
      tab.addEventListener('click', function() {
        tabs.querySelectorAll('.wds-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      });
    });
  });

  // ── Horizontal / topnav nav items ─────────────────────────────
  document.querySelectorAll('.wks-horiz-nav-item, .wks-topnav-item').forEach(item => {
    item.addEventListener('click', function() {
      this.closest('nav, .wks-horiz-nav, .wks-topbar-nav')
        ?.querySelectorAll('.wks-horiz-nav-item, .wks-topnav-item')
        .forEach(i => i.classList.remove('active'));
      this.classList.add('active');
    });
  });
</script>

</body>
</html>
