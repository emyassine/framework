<style>
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
</style>
