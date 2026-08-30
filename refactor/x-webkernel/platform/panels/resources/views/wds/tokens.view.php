{{--
  WDS tokens + palettes + reset. Always in <head>. Critical path only.
  Text and surfaces use semantic aliases so dark mode inverts stops.
--}}
<style>
:root {
{!! \Webkernel\Platform\Colors\Color::root_css() !!}

  --wds-topbar-height: 50px;
  --wds-rail-width: 4rem;
  --wds-nav-width: clamp(280px, 16vw, 340px);
  --wds-nav-width-collapsed: 0px;
  --wds-aside-width: 20rem;
  --wds-radius: 6px;
  --wds-radius-lg: 0.75rem;
  --wds-radius-full: 9999px;
  --wds-font-sans: var(--wts-font-stack, system-ui, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol");
  --wds-font-mono: var(--wts-font-mono, ui-monospace, Consolas, monospace);
  --wds-text-xs: 0.6875rem;
  --wds-text-sm: 0.8125rem;
  --wds-text-base: 0.875rem;
  --wds-text-md: 1rem;
  --wds-text-lg: 1.125rem;
  --wds-text-xl: 1.375rem;
  --wds-text-2xl: 1.625rem;
  --wds-transition: 180ms ease;

  --wds-bg: var(--gray-50);
  --wds-bg-subtle: var(--gray-100);
  --wds-surface: var(--gray-50);
  --wds-border: var(--gray-200);
  --wds-border-strong: var(--gray-300);
  --wds-text: var(--gray-900);
  --wds-text-muted: var(--gray-600);
  --wds-text-faint: var(--gray-500);

  --wds-rail-bg: var(--wds-surface);
  --wds-nav-bg: var(--color-gray-900);
  --wds-nav-text: var(--color-gray-400);
}

[data-wds-theme="dark"] {
{!! \Webkernel\Platform\Colors\Color::dark_root_css() !!}
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 16px; color-scheme: light; }
[data-wds-theme="dark"] { color-scheme: dark; }
body {
  font-family: var(--wds-font-sans);
  font-size: 14px;
  background: var(--wds-bg);
  color: var(--wds-text);
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}
a { color: inherit; text-decoration: none; }
button { cursor: pointer; font: inherit; border: none; background: none; color: inherit; }
svg { display: block; flex-shrink: 0; }
ul { list-style: none; }

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
