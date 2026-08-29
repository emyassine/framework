{{--
  WDS tokens + palettes + reset. Always in <head>. Critical path only.
--}}
<style>
:root {
{!! \Webkernel\Platform\Colors\Color::root_css() !!}

  --wds-topbar-height: 50px;
  --wds-nav-header-height: 65px;
  --wds-nav-width: clamp(280px, 16vw, 340px);
  --wds-nav-width-collapsed: 72px;
  --wds-radius-sm: 0.25rem;
  --wds-radius: 6px;
  --wds-radius-lg: 0.75rem;
  --wds-radius-full: 9999px;
  --wds-font-sans: var(--wts-font-stack, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif);
  --wds-font-mono: var(--wts-font-mono, "JetBrains Mono", "Fira Code", "Cascadia Code", Consolas, monospace);
  --wds-text-xs: 0.6875rem;
  --wds-text-sm: 0.8125rem;
  --wds-text-base: 0.875rem;
  --wds-text-md: 1rem;
  --wds-text-lg: 1.125rem;
  --wds-text-xl: 1.375rem;
  --wds-text-2xl: 1.625rem;
  --wds-transition: 180ms ease;

  --wds-bg: hsl(220 20% 97%);
  --wds-bg-subtle: hsl(220 16% 94%);
  --wds-surface: #fff;
  --wds-border: hsl(220 16% 88%);
  --wds-border-strong: hsl(220 12% 78%);
  --wds-text: hsl(220 20% 16%);
  --wds-text-muted: hsl(220 10% 42%);
  --wds-text-faint: hsl(220 8% 58%);

  --wds-nav-bg: hsl(220 40% 12%);
  --wds-nav-text: hsl(220 18% 88%);
  --wds-nav-muted: hsl(220 12% 70%);
  --wds-nav-edge: color-mix(in srgb, var(--wds-nav-text) 20%, var(--wds-nav-bg));
  --wds-nav-hover-bg: color-mix(in srgb, var(--wds-nav-text) 10%, var(--wds-nav-bg));
  --wds-nav-active-bg: var(--primary-600, hsl(220 85% 50%));
  --wds-nav-active-text: #fff;
}

[data-wds-theme="dark"] {
  --wds-bg: hsl(220 18% 8%);
  --wds-bg-subtle: hsl(220 16% 12%);
  --wds-surface: hsl(220 16% 11%);
  --wds-border: hsl(220 12% 18%);
  --wds-border-strong: hsl(220 10% 26%);
  --wds-text: hsl(220 16% 92%);
  --wds-text-muted: hsl(220 10% 64%);
  --wds-text-faint: hsl(220 8% 48%);
  --wds-nav-bg: hsl(220 22% 9%);
  --wds-nav-text: hsl(220 14% 82%);
  --primary-50: var(--color-blue-950);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 16px; }
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
button { cursor: pointer; font: inherit; border: none; background: none; }
svg { display: block; flex-shrink: 0; }
ul { list-style: none; }

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
