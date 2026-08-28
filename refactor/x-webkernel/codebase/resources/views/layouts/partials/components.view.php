<style>
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
