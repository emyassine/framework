{{--
  Panel chrome. Loaded by layouts.page only.
--}}
<style>
    .wds-layout {
      display: flex;
      min-height: 100vh;
    }

    .wds-nav {
      --wds-co: var(--wds-nav-text);
      color: var(--wds-co);
      height: 100vh;
      height: 100dvh;
      width: var(--wds-nav-width);
      flex: 0 0 var(--wds-nav-width);
      background: var(--wds-nav-bg);
      background-clip: padding-box;
      border-inline-end: 1px solid var(--wds-nav-edge);
      position: sticky;
      top: 0;
      z-index: 50;
      display: flex;
      flex-direction: column;
    }

    [data-wds-sidebar="collapsed"] .wds-nav {
      width: var(--wds-nav-width-collapsed);
      flex-basis: var(--wds-nav-width-collapsed);
    }

    .wds-nav-brand {
      flex: 0 0 auto;
      height: var(--wds-nav-header-height);
      padding: 0 20px;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      box-shadow: color-mix(in srgb, var(--wds-co) 15%, transparent) 0 1px 0;
      position: relative;
      z-index: 2;
      overflow: hidden;
      color: inherit;
    }
    .wds-nav-brand-mark {
      width: 28px;
      height: 28px;
      flex: 0 0 auto;
      display: grid;
      place-content: center;
      overflow: hidden;
    }
    .wds-nav-brand-mark img {
      width: 28px;
      height: 28px;
      object-fit: contain;
      display: block;
    }
    .wds-nav-brand-name {
      font-size: 20px;
      font-weight: 500;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .wds-logo-icon {
      width: 28px;
      height: 28px;
      border-radius: var(--wds-radius);
      background: var(--primary-600, hsl(220 85% 50%));
      color: #fff;
      display: grid;
      place-content: center;
    }

    .wds-nav-scroll {
      flex: 1 1 100%;
      overflow-y: auto;
      overscroll-behavior: contain;
      padding: 1em;
      scrollbar-width: thin;
      scrollbar-color: color-mix(in srgb, var(--wds-co) 40%, transparent) transparent;
    }

    .wds-nav-search {
      width: 100%;
      display: flex;
      align-items: center;
      gap: 0.8em;
      padding: 0.65em 1.3em;
      margin-bottom: 1.5em;
      border-radius: var(--wds-radius);
      background: color-mix(in srgb, currentColor 10%, transparent);
      color: inherit;
      cursor: text;
    }
    .wds-nav-search:focus-within {
      background: color-mix(in srgb, currentColor 16%, transparent);
    }
    .wds-nav-search input {
      flex: 1;
      min-width: 0;
      border: 0;
      background: transparent;
      color: inherit;
      outline: none;
      font: inherit;
    }
    .wds-nav-search input::placeholder {
      color: var(--wds-nav-muted);
      opacity: 1;
    }

    .wds-nav-section {
      font-weight: 700;
      font-size: max(11px, 0.8em);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      background: var(--wds-nav-bg);
      position: sticky;
      top: 0;
      z-index: 1;
      padding: 1em 1.3em 0.4em;
      display: flex;
      align-items: center;
      gap: 0.75em;
      color: var(--wds-nav-text);
    }
    .wds-nav-section-mark {
      width: 18px;
      height: 18px;
      flex: 0 0 auto;
      overflow: hidden;
      display: grid;
      place-content: center;
    }
    .wds-nav-section-mark img {
      width: 18px;
      height: 18px;
      display: block;
    }
    .wds-nav-list > li + li .wds-nav-section {
      margin-top: clamp(0.8em, 3vh, 2.5em);
    }

    .wds-nav-link {
      width: 100%;
      display: flex;
      align-items: center;
      padding: 0.65em 1.3em;
      color: var(--wds-nav-text);
      font-weight: 550;
      border-radius: var(--wds-radius);
    }
    .wds-nav-link:hover {
      color: #fff;
      background: var(--wds-nav-hover-bg);
    }
    .wds-nav-link[aria-current="page"],
    .wds-nav-link.wds-active {
      background: var(--wds-nav-active-bg);
      color: var(--wds-nav-active-text);
    }
    .wds-nav-icon {
      flex: 0 0 1.3em;
      text-align: center;
      margin-inline-end: 0.8em;
      opacity: 0.4;
      display: grid;
      place-content: center;
    }
    .wds-nav-icon svg {
      width: 1em;
      height: 1em;
      stroke: currentColor;
      fill: none;
    }
    .wds-nav-link:hover .wds-nav-icon,
    .wds-nav-link.wds-active .wds-nav-icon,
    .wds-nav-link[aria-current="page"] .wds-nav-icon {
      opacity: 1;
    }

    .wds-nav-logo--favicon img { width: 16px; height: 16px; object-fit: contain; border-radius: 0; }
    .wds-nav-logo--round img { border-radius: 50%; object-fit: cover; }
    .wds-nav-logo--square img { border-radius: 6px; object-fit: cover; }

    [data-wds-sidebar="collapsed"] .wds-nav-brand-name,
    [data-wds-sidebar="collapsed"] .wds-nav-search,
    [data-wds-sidebar="collapsed"] .wds-nav-section span,
    [data-wds-sidebar="collapsed"] .wds-nav-link span:not(.wds-nav-icon) {
      display: none;
    }
    [data-wds-sidebar="collapsed"] .wds-nav-brand,
    [data-wds-sidebar="collapsed"] .wds-nav-link,
    [data-wds-sidebar="collapsed"] .wds-nav-section {
      justify-content: center;
      padding-inline: 0.5em;
    }
    [data-wds-sidebar="collapsed"] .wds-nav-icon {
      margin-inline-end: 0;
    }
    [data-wds-sidebar="collapsed"] .wds-nav-scroll {
      padding: 0.75em 0.4em;
    }

    .wds-main-ctn {
      flex: 1 1 100%;
      min-width: 0;
      display: flex;
      flex-direction: column;
    }

    .wds-topbar {
      height: var(--wds-topbar-height);
      display: flex;
      align-items: center;
      padding: 0 12px;
      gap: 0.75rem;
      background: var(--wds-surface);
      color: var(--wds-text);
      position: sticky;
      top: 0;
      z-index: 40;
      box-shadow: color-mix(in srgb, currentColor 12%, transparent) 0 1px 0;
    }

    .wds-sidebar-open-btn {
      width: 36px;
      height: 36px;
      display: grid;
      place-content: center;
      border-radius: var(--wds-radius);
      color: var(--wds-text-muted);
      flex-shrink: 0;
    }
    .wds-sidebar-open-btn:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }
    .wds-sidebar-open-btn svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
    }

    .wds-breadcrumbs {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 12px;
      color: var(--wds-text-muted);
    }

    .wds-topbar-end {
      margin-inline-start: auto;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .wds-icon-btn {
      min-width: 40px;
      min-height: 40px;
      display: grid;
      place-content: center;
      border-radius: 4px;
      color: var(--wds-text-muted);
    }
    .wds-icon-btn:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }
    .wds-icon-btn svg {
      width: 15px;
      height: 15px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
    }

    .wds-main {
      flex: 1;
      padding: 25px 20px 20px;
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 1em;
    }

    .wds-icon,
    .wds-icon-svg,
    .wds-icon svg {
      width: 16px;
      height: 16px;
      display: block;
      flex-shrink: 0;
    }

    .wds-avatar {
      width: 36px;
      height: 36px;
      border-radius: var(--wds-radius-full);
      background: var(--primary-200, hsl(220 70% 84%));
      color: var(--primary-800, hsl(220 50% 28%));
      font-size: 0.75rem;
      font-weight: 700;
      display: grid;
      place-content: center;
      flex-shrink: 0;
    }

    .wds-user-menu { position: relative; }
    .wds-user-menu-trigger {
      display: flex;
      align-items: center;
      gap: 0.5em;
      padding: 0 10px;
      min-height: 40px;
      border-radius: 4px;
      color: inherit;
    }
    .wds-user-menu-trigger:hover { background: var(--wds-bg-subtle); }
    .wds-user-menu-name { font-size: 13px; font-weight: 600; white-space: nowrap; }
    .wds-user-menu-chevron { width: 14px; height: 14px; color: var(--wds-text-muted); opacity: 0.5; }
    .wds-user-menu-panel {
      display: none;
      position: absolute;
      inset-inline-end: 0;
      top: calc(100% + 0.5rem);
      min-width: 180px;
      background: var(--wds-surface);
      border: 1px solid var(--wds-border);
      border-radius: 8px;
      box-shadow: 0 8px 24px hsl(220 40% 10% / 0.16);
      padding: 4px;
      z-index: 60;
    }
    .wds-user-menu.wds-open .wds-user-menu-panel {
      display: flex;
      flex-direction: column;
    }
    .wds-user-menu-panel a {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6em 0.9em;
      border-radius: 4px;
      color: var(--wds-text-muted);
      font-size: 13px;
      font-weight: 500;
    }
    .wds-user-menu-panel a:hover {
      background: var(--wds-bg-subtle);
      color: var(--wds-text);
    }

    .wds-form { display: flex; flex-direction: column; max-width: 40em; }
    .wds-form-row {
      display: grid;
      grid-template-columns: max(12em, 28%) 1fr;
      gap: 1.5em;
      padding: 1.5em;
      border-bottom: 1px solid var(--wds-border);
      align-items: start;
    }
    .wds-form-row label { font-weight: 500; padding-top: 0.4em; }
    .wds-form-row input,
    .wds-form-row select {
      max-width: 28em;
      width: 100%;
      font: inherit;
      color: var(--wds-text);
      background: var(--wds-surface);
      border: 1px solid var(--wds-border-strong);
      border-radius: var(--wds-radius);
      padding: 0.55em 0.8em;
    }
    .wds-form-actions {
      position: sticky;
      bottom: 0;
      padding: 1em 1.5em;
      background: var(--wds-surface);
      border-top: 1px solid var(--wds-border);
    }
    .wds-flash {
      padding: 0.8em 1em;
      border-radius: var(--wds-radius);
      background: color-mix(in srgb, var(--primary-600, hsl(220 85% 50%)) 12%, transparent);
    }

    @media (max-width: 979px) {
      .wds-nav { display: none; }
      [data-wds-sidebar="expanded"] .wds-nav {
        display: flex;
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 80;
        box-shadow: 0 0 32px hsl(220 40% 4% / 0.35);
      }
    }
</style>
