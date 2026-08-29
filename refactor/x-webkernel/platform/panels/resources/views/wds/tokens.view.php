{{--
  WDS tokens + palettes + reset. Always in <head>. Critical path only.
--}}
<style>
:root {
{!! \Webkernel\Platform\Colors\Color::root_css() !!}

  --wds-space-outer: 0.65rem;
  --wds-space-inner: 0.65rem;
  --wds-space-top: 0.65rem;
  --wds-space-bottom: 0.65rem;
  --wds-bottom-clearance: 3.6rem;
  --wds-topbar-height: 52px;
  --wds-sidebar-width: 240px;
  --wds-sidebar-width-collapsed: 56px;
  --wds-sidebar-toggle-height: 2.5rem;
  --wds-sidebar-padding-x: 1rem;
  --wds-radius-sm: 0.25rem;
  --wds-radius: 0.5rem;
  --wds-radius-lg: 0.75rem;
  --wds-radius-xl: 1rem;
  --wds-radius-full: 9999px;
  --wds-radius-container: 7px;
  --wds-radius-content: 13px;
  --wds-backdrop-blur: 10px;
  --wds-shadow-sm: 0 1px 2px 0 oklch(0 0 0 / 0.05);
  --wds-shadow: 0 1px 3px 0 oklch(0 0 0 / 0.1), 0 1px 2px -1px oklch(0 0 0 / 0.1);
  --wds-shadow-md: 0 4px 6px -1px oklch(0 0 0 / 0.08), 0 2px 4px -2px oklch(0 0 0 / 0.08);
  --wds-shadow-y: 2px;
  --wds-shadow-blur: 4px;
  --wds-shadow-spread: 0px;
  --wds-shadow-opacity: 0.06;
  --wds-shadow-border-opacity: 0.08;
  --wds-shadow-dark-opacity: 0.3;
  --wds-shadow-dark-border-opacity: 0.08;
  --wds-scrollbar-size: 3.5px;
  --wds-scrollbar-opacity: 0.7;
  --wds-scrollbar-opacity-hover: 0.9;
  --wds-font-sans: var(--wts-font-stack, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif);
  --wds-font-mono: var(--wts-font-mono, "JetBrains Mono", "Fira Code", "Cascadia Code", Consolas, monospace);
  --wds-text-xs: 0.6875rem;
  --wds-text-sm: 0.8125rem;
  --wds-text-base: 0.875rem;
  --wds-text-md: 1rem;
  --wds-text-lg: 1.125rem;
  --wds-text-xl: 1.375rem;
  --wds-text-2xl: 1.75rem;
  --wds-text-3xl: 2.25rem;
  --wds-transition: 220ms cubic-bezier(0.4, 0, 0.2, 1);

  --wds-bg: var(--gray-50);
  --wds-bg-subtle: var(--gray-100);
  --wds-surface: #ffffff;
  --wds-surface-raise: #ffffff;
  --wds-border: var(--gray-200);
  --wds-border-strong: var(--gray-300);
  --wds-text: var(--gray-900);
  --wds-text-muted: var(--gray-500);
  --wds-text-faint: var(--gray-400);
  --wds-text-on-dark: var(--gray-50);
  --wds-color-background: var(--gray-100);
  --wds-color-topbar: var(--gray-100);
}

[data-wds-theme="dark"] {
  --wds-bg: var(--gray-950);
  --wds-bg-subtle: var(--gray-900);
  --wds-surface: var(--gray-900);
  --wds-surface-raise: var(--gray-800);
  --wds-border: var(--gray-800);
  --wds-border-strong: var(--gray-700);
  --wds-text: var(--gray-50);
  --wds-text-muted: var(--gray-400);
  --wds-text-faint: var(--gray-600);
  --wds-color-background: var(--gray-950);
  --wds-color-topbar: var(--gray-950);
  --primary-50: var(--color-blue-950);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 16px; }
body {
  font-family: var(--wds-font-sans);
  font-size: var(--wds-text-base);
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

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
