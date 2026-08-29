<style>
    /* Page header */
    .webkernel-shell-page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
    }
    .webkernel-shell-page-title {
      font-size: var(--webkernel-design-font-size-2xl);
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.2;
    }
    .webkernel-shell-page-desc {
      margin-top: 0.25rem;
      font-size: var(--webkernel-design-font-size-sm);
      color: var(--webkernel-design-text-muted);
    }
    .webkernel-shell-page-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-shrink: 0;
    }

    /* ============================================================
       Webkernel Design CARD COMPONENT
    ============================================================ */
    .webkernel-design-card {
      background: var(--webkernel-design-surface);
      border: 1px solid var(--webkernel-design-border);
      border-radius: var(--webkernel-design-radius-lg);
      box-shadow: var(--webkernel-design-shadow-sm);
      overflow: hidden;
    }
    .webkernel-design-card-header {
      padding: 1.25rem 1.5rem 0;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
    }
    .webkernel-design-card-title {
      font-size: var(--webkernel-design-font-size-base);
      font-weight: 600;
      letter-spacing: -0.01em;
    }
    .webkernel-design-card-desc {
      font-size: var(--webkernel-design-font-size-sm);
      color: var(--webkernel-design-text-muted);
      margin-top: 0.125rem;
    }
    .webkernel-design-card-body {
      padding: 1.25rem 1.5rem 1.5rem;
    }

    /* ============================================================
       KPI METRIC CARDS
    ============================================================ */
    .webkernel-shell-metrics-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }

    .webkernel-shell-metric-card {
      background: var(--webkernel-design-surface);
      border: 1px solid var(--webkernel-design-border);
      border-radius: var(--webkernel-design-radius-lg);
      padding: 1.25rem 1.5rem;
      box-shadow: var(--webkernel-design-shadow-sm);
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .webkernel-shell-metric-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .webkernel-shell-metric-label {
      font-size: var(--webkernel-design-font-size-sm);
      font-weight: 500;
      color: var(--webkernel-design-text-muted);
    }
    .webkernel-shell-metric-icon {
      width: 16px;
      height: 16px;
      stroke: var(--webkernel-design-text-muted);
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .webkernel-shell-metric-value {
      font-size: var(--webkernel-design-font-size-2xl);
      font-weight: 700;
      letter-spacing: -0.03em;
      line-height: 1.1;
    }
    .webkernel-shell-metric-trend {
      font-size: var(--webkernel-design-font-size-xs);
      color: var(--webkernel-design-text-muted);
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    .webkernel-shell-metric-trend.up   { color: var(--webkernel-design-success-text); }
    .webkernel-shell-metric-trend.down { color: var(--webkernel-design-danger-text); }
    .webkernel-shell-metric-trend svg {
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
    .webkernel-shell-bento {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    .webkernel-shell-bento--wide { grid-template-columns: 3fr 2fr; }

    /* ============================================================
       BAR CHART (pure CSS)
    ============================================================ */
    .webkernel-design-chart {
      display: flex;
      align-items: flex-end;
      gap: 6px;
      height: 160px;
      padding-top: 1rem;
      margin-top: 0.75rem;
    }
    .webkernel-design-chart__col {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.375rem;
      height: 100%;
      justify-content: flex-end;
    }
    .webkernel-design-chart__bar {
      width: 100%;
      background: var(--webkernel-design-accent);
      border-radius: var(--webkernel-design-radius-sm) var(--webkernel-design-radius-sm) 0 0;
      opacity: 0.85;
      transition: opacity 0.15s;
      min-height: 4px;
    }
    .webkernel-design-chart__bar:hover { opacity: 1; }
    .webkernel-design-chart__bar--secondary {
      background: var(--webkernel-color-primary-200);
    }
    [data-webkernel-design-theme="dark"] .webkernel-design-chart__bar--secondary {
      background: var(--webkernel-color-primary-800);
    }
    .webkernel-design-chart__lbl {
      font-size: var(--webkernel-design-font-size-xs);
      color: var(--webkernel-design-text-faint);
      white-space: nowrap;
    }

    /* Mini sparkline */
    .webkernel-design-sparkline {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 36px;
    }
    .webkernel-design-sparkline__bar {
      flex: 1;
      background: var(--webkernel-design-accent);
      border-radius: 2px 2px 0 0;
      opacity: 0.6;
    }

    /* ============================================================
       TABLE
    ============================================================ */
    .webkernel-design-table-wrap {
      overflow-x: auto;
      margin-top: 0.75rem;
    }
    .webkernel-design-table {
      width: 100%;
      border-collapse: collapse;
      font-size: var(--webkernel-design-font-size-sm);
      text-align: left;
    }
    .webkernel-design-table thead th {
      padding: 0.625rem 0.75rem;
      font-weight: 500;
      color: var(--webkernel-design-text-muted);
      border-bottom: 1px solid var(--webkernel-design-border);
      white-space: nowrap;
    }
    .webkernel-design-table tbody tr {
      transition: background var(--webkernel-shell-transition);
    }
    .webkernel-design-table tbody tr:hover {
      background: var(--webkernel-design-bg-subtle);
    }
    .webkernel-design-table tbody td {
      padding: 0.625rem 0.75rem;
      border-bottom: 1px solid var(--webkernel-design-border);
    }
    .webkernel-design-table tbody tr:last-child td {
      border-bottom: none;
    }

    /* ============================================================
       BADGE COMPONENT
    ============================================================ */
    .webkernel-design-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.125rem 0.5rem;
      border-radius: var(--webkernel-design-radius-full);
      font-size: var(--webkernel-design-font-size-xs);
      font-weight: 600;
      border: 1px solid transparent;
      white-space: nowrap;
    }
    .webkernel-design-badge::before {
      content: '';
      display: block;
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: currentColor;
    }
    .webkernel-design-badge--success {
      background: var(--webkernel-design-success-bg);
      color: var(--webkernel-design-success-text);
    }
    .webkernel-design-badge--warning {
      background: var(--webkernel-design-warning-bg);
      color: var(--webkernel-design-warning-text);
    }
    .webkernel-design-badge--danger {
      background: var(--webkernel-design-danger-bg);
      color: var(--webkernel-design-danger-text);
    }
    .webkernel-design-badge--info {
      background: var(--webkernel-design-info-bg);
      color: var(--webkernel-design-info-text);
    }
    .webkernel-design-badge--neutral {
      background: var(--webkernel-design-bg-subtle);
      color: var(--webkernel-design-text-muted);
    }

    /* ============================================================
       BUTTON COMPONENT
    ============================================================ */
    .webkernel-design-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.4375rem 0.875rem;
      font-size: var(--webkernel-design-font-size-sm);
      font-weight: 500;
      border-radius: var(--webkernel-design-radius);
      border: 1px solid transparent;
      cursor: pointer;
      transition: background var(--webkernel-shell-transition), color var(--webkernel-shell-transition), border-color var(--webkernel-shell-transition), box-shadow var(--webkernel-shell-transition);
      white-space: nowrap;
      line-height: 1.4;
    }
    .webkernel-design-btn svg {
      width: 14px;
      height: 14px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .webkernel-design-btn--primary {
      background: var(--webkernel-design-accent);
      color: #fff;
    }
    .webkernel-design-btn--primary:hover {
      background: var(--webkernel-design-accent-hover);
    }
    .webkernel-design-btn--outline {
      background: var(--webkernel-design-surface);
      border-color: var(--webkernel-design-border);
      color: var(--webkernel-design-text);
    }
    .webkernel-design-btn--outline:hover {
      background: var(--webkernel-design-bg-subtle);
      border-color: var(--webkernel-design-border-strong);
    }
    .webkernel-design-btn--ghost {
      background: transparent;
      color: var(--webkernel-design-text-muted);
    }
    .webkernel-design-btn--ghost:hover {
      background: var(--webkernel-design-bg-subtle);
      color: var(--webkernel-design-text);
    }
    .webkernel-design-btn--sm {
      padding: 0.25rem 0.625rem;
      font-size: var(--webkernel-design-font-size-xs);
    }
    .webkernel-design-btn--xs {
      padding: 0.125rem 0.5rem;
      font-size: var(--webkernel-design-font-size-xs);
      gap: 0.25rem;
    }
    .webkernel-design-btn--lg {
      padding: 0.625rem 1.125rem;
      font-size: var(--webkernel-design-font-size-base);
    }
    .webkernel-design-btn--xl {
      padding: 0.75rem 1.375rem;
      font-size: var(--webkernel-design-font-size-md);
    }
    .webkernel-design-btn--danger {
      background: var(--webkernel-design-danger-text);
      color: #fff;
    }
    .webkernel-design-btn--danger:hover {
      filter: brightness(0.92);
    }
    .webkernel-design-btn--gray {
      background: var(--webkernel-design-bg-subtle);
      color: var(--webkernel-design-text);
      border-color: var(--webkernel-design-border);
    }
    .webkernel-design-btn--gray:hover {
      background: var(--webkernel-design-border);
    }
    .webkernel-design-btn--info {
      background: var(--webkernel-design-info-text);
      color: #fff;
    }
    .webkernel-design-btn--success {
      background: var(--webkernel-design-success-text);
      color: #fff;
    }
    .webkernel-design-btn--warning {
      background: var(--webkernel-design-warning-text);
      color: #fff;
    }
    .webkernel-design-btn--outline.webkernel-design-btn--primary {
      background: transparent;
      color: var(--webkernel-design-accent-text);
      border-color: var(--webkernel-design-accent);
    }
    .webkernel-design-btn--outline.webkernel-design-btn--danger {
      background: transparent;
      color: var(--webkernel-design-danger-text);
      border-color: var(--webkernel-design-danger-text);
    }
    .webkernel-design-btn--disabled,
    .webkernel-design-btn[disabled],
    .webkernel-design-btn[aria-disabled="true"] {
      opacity: 0.55;
      pointer-events: none;
      cursor: not-allowed;
    }
    a.webkernel-design-btn { text-decoration: none; color: inherit; }
    .webkernel-design-btn__label { display: inline-flex; align-items: center; }
    .webkernel-design-btn__badge {
      margin-inline-start: 0.25rem;
    }
    .webkernel-design-btn-group {
      display: inline-flex;
      align-items: stretch;
    }
    .webkernel-design-btn-group > .webkernel-design-btn {
      border-radius: 0;
    }
    .webkernel-design-btn-group > .webkernel-design-btn:first-child {
      border-top-left-radius: var(--webkernel-design-radius);
      border-bottom-left-radius: var(--webkernel-design-radius);
    }
    .webkernel-design-btn-group > .webkernel-design-btn:last-child {
      border-top-right-radius: var(--webkernel-design-radius);
      border-bottom-right-radius: var(--webkernel-design-radius);
    }
    .webkernel-design-btn-group > .webkernel-design-btn + .webkernel-design-btn {
      margin-inline-start: -1px;
    }

    /* ============================================================
       ACTIVITY / RECENT LIST
    ============================================================ */
    .webkernel-shell-activity-list {
      display: flex;
      flex-direction: column;
      gap: 0;
      margin-top: 0.75rem;
    }
    .webkernel-shell-activity-item {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--webkernel-design-border);
    }
    .webkernel-shell-activity-item:last-child { border-bottom: none; }
    .webkernel-shell-activity-dot {
      width: 28px;
      height: 28px;
      border-radius: var(--webkernel-design-radius-full);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 0.625rem;
      font-weight: 700;
    }
    .webkernel-shell-activity-dot--success { background: var(--webkernel-design-success-bg); color: var(--webkernel-design-success-text); }
    .webkernel-shell-activity-dot--warning { background: var(--webkernel-design-warning-bg); color: var(--webkernel-design-warning-text); }
    .webkernel-shell-activity-dot--info    { background: var(--webkernel-design-info-bg);    color: var(--webkernel-design-info-text); }
    .webkernel-shell-activity-dot--danger  { background: var(--webkernel-design-danger-bg);  color: var(--webkernel-design-danger-text); }

    .webkernel-shell-activity-body { flex: 1; min-width: 0; }
    .webkernel-shell-activity-title {
      font-size: var(--webkernel-design-font-size-sm);
      font-weight: 500;
    }
    .webkernel-shell-activity-meta {
      font-size: var(--webkernel-design-font-size-xs);
      color: var(--webkernel-design-text-muted);
      margin-top: 0.125rem;
    }
    .webkernel-shell-activity-time {
      font-size: var(--webkernel-design-font-size-xs);
      color: var(--webkernel-design-text-faint);
      flex-shrink: 0;
    }

    /* ============================================================
       PROGRESS BAR
    ============================================================ */
    .webkernel-design-progress {
      height: 6px;
      border-radius: var(--webkernel-design-radius-full);
      background: var(--webkernel-design-bg-subtle);
      overflow: hidden;
    }
    .webkernel-design-progress__bar {
      height: 100%;
      border-radius: var(--webkernel-design-radius-full);
      background: var(--webkernel-design-accent);
      transition: width 0.4s ease;
    }

    /* ============================================================
       TAB BAR
    ============================================================ */
    .webkernel-design-tabs {
      display: flex;
      gap: 0;
      border-bottom: 1px solid var(--webkernel-design-border);
      margin-bottom: 1rem;
    }
    .webkernel-design-tab {
      padding: 0.5rem 1rem;
      font-size: var(--webkernel-design-font-size-sm);
      font-weight: 500;
      color: var(--webkernel-design-text-muted);
      border-bottom: 2px solid transparent;
      margin-bottom: -1px;
      cursor: pointer;
      transition: color var(--webkernel-shell-transition), border-color var(--webkernel-shell-transition);
    }
    .webkernel-design-tab:hover { color: var(--webkernel-design-text); }
    .webkernel-design-tab.active {
      color: var(--webkernel-design-accent-text);
      border-bottom-color: var(--webkernel-design-accent);
    }

    /* ============================================================
       STAT DONUT (CSS only)
    ============================================================ */
    .webkernel-design-donut-wrap {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      margin-top: 0.75rem;
    }
    .webkernel-design-donut {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: conic-gradient(var(--webkernel-design-accent) 0% 72%, var(--webkernel-design-bg-subtle) 72% 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      flex-shrink: 0;
    }
    .webkernel-design-donut::after {
      content: '';
      position: absolute;
      width: 54px;
      height: 54px;
      border-radius: 50%;
      background: var(--webkernel-design-surface);
    }
    .webkernel-design-donut__label {
      position: relative;
      z-index: 1;
      font-size: var(--webkernel-design-font-size-sm);
      font-weight: 700;
    }
    .webkernel-design-donut-legend { display: flex; flex-direction: column; gap: 0.5rem; }
    .webkernel-design-legend-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: var(--webkernel-design-font-size-xs);
    }
    .webkernel-design-legend-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* ============================================================
       RESPONSIVE
    ============================================================ */
    @media (max-width: 1100px) {
      .webkernel-shell-metrics-grid { grid-template-columns: repeat(2, 1fr); }
      .webkernel-shell-bento--wide  { grid-template-columns: 1fr; }
    }
    @media (max-width: 860px) {
      .webkernel-shell-bento { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      .webkernel-shell-metrics-grid { grid-template-columns: 1fr 1fr; }
      .webkernel-shell-content { padding: 1rem; gap: 1rem; }
      .webkernel-shell-page-header { flex-direction: column; }
      .webkernel-shell-layout-switcher { display: none; }
    }

    /* ============================================================
       UTILITY
    ============================================================ */
    .u-flex-gap-sm { display: flex; align-items: center; gap: 0.375rem; }
    .u-ml-auto { margin-left: auto; }
    .u-text-muted { color: var(--webkernel-design-text-muted); }
    .u-text-sm { font-size: var(--webkernel-design-font-size-sm); }
    .u-font-mono { font-family: var(--webkernel-design-font-mono); }
</style>
