<style>

/* webkernel::stat */
.wk-stat {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    height: 100%;
    box-sizing: border-box;
    border-radius: var(--radius-lg) !important;
    padding: 0.9rem 0.95rem 0.8rem;
    background: rgb(255 255 255);
    box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
    outline: 1px solid color-mix(in oklab, var(--gray-950, #030712) 6%, transparent);
}
.dark .wk-stat,
[data-theme="dark"] .wk-stat,
:where(.dark, [data-theme="dark"], .fi-body.dark) .wk-stat {
    background: color-mix(in oklab, var(--gray-900, #111827) 100%, transparent);
    outline-color: color-mix(in oklab, white 10%, transparent);
}
.wk-stat-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.65rem;
}
.wk-stat-main { min-width: 0; flex: 1 1 auto; }
.wk-stat-label {
    font-size: 0.8125rem;
    font-weight: 500;
    line-height: 1.25;
    color: color-mix(in oklab, var(--gray-500, #6b7280) 100%, transparent);
}
.dark .wk-stat-label,
[data-theme="dark"] .wk-stat-label,
:where(.dark, [data-theme="dark"], .fi-body.dark) .wk-stat-label {
    color: color-mix(in oklab, var(--gray-400, #9ca3af) 100%, transparent);
}
.wk-stat-value {
    margin-top: 0.12rem;
    font-size: 1.625rem;
    font-weight: 600;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: var(--gray-950, #030712);
}
.dark .wk-stat-value,
[data-theme="dark"] .wk-stat-value,
:where(.dark, [data-theme="dark"], .fi-body.dark) .wk-stat-value {
    color: #fff;
}
.wk-stat-icon {
    flex-shrink: 0;
    width: 2.15rem;
    height: 2.15rem;
    border-radius: 0.6rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in oklab, var(--primary-500, #3b82f6) 12%, transparent);
    color: var(--primary-600, #2563eb);
}
.wk-stat-icon.fi-color-success { background: color-mix(in oklab, var(--success-500, #22c55e) 14%, transparent); color: var(--success-600, #16a34a); }
.wk-stat-icon.fi-color-info { background: color-mix(in oklab, var(--info-500, #0ea5e9) 14%, transparent); color: var(--info-600, #0284c7); }
.wk-stat-icon.fi-color-warning { background: color-mix(in oklab, var(--warning-500, #f59e0b) 16%, transparent); color: var(--warning-600, #d97706); }
.wk-stat-icon.fi-color-danger { background: color-mix(in oklab, var(--danger-500, #ef4444) 14%, transparent); color: var(--danger-600, #dc2626); }
.wk-stat-icon.fi-color-primary { background: color-mix(in oklab, var(--primary-500, #3b82f6) 14%, transparent); color: var(--primary-600, #2563eb); }
.wk-stat-icon.fi-color-gray { background: color-mix(in oklab, var(--gray-500, #6b7280) 12%, transparent); color: var(--gray-600, #4b5563); }
.wk-stat-icon-svg { width: 1.05rem !important; height: 1.05rem !important; }

.wk-stat-bottom-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.4rem;
    min-height: 1.5rem;
    margin-top: auto;
}
.wk-stat-bottom-start {
    min-width: 0;
    flex: 1 1 auto;
}
.wk-stat-description {
    display: inline-flex;
    align-items: center;
    gap: 0.28rem;
    font-size: 0.78rem;
    line-height: 1.25;
    max-width: 100%;
    cursor: default;
    color: color-mix(in oklab, var(--gray-500, #6b7280) 100%, transparent);
}
.wk-stat-description.fi-color-success { color: var(--success-600, #16a34a); }
.wk-stat-description.fi-color-info { color: var(--info-600, #0284c7); }
.wk-stat-description.fi-color-warning { color: var(--warning-600, #d97706); }
.wk-stat-description.fi-color-danger { color: var(--danger-600, #dc2626); }
.wk-stat-description.fi-color-primary { color: var(--primary-600, #2563eb); }
.wk-stat-description-icon { width: 0.85rem !important; height: 0.85rem !important; flex-shrink: 0; }
.wk-stat-description-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.wk-stat-chart-wrap {
    position: relative;
    margin-top: 0.15rem;
    min-height: 1.65rem;
}
.wk-stat-chart {
    width: 100%;
    padding-inline-end: 1.75rem;
    color: var(--primary-500, #3b82f6);
}
.wk-stat-chart.fi-color-success { color: var(--success-500, #22c55e); }
.wk-stat-chart.fi-color-info { color: var(--info-500, #0ea5e9); }
.wk-stat-chart.fi-color-warning { color: var(--warning-500, #f59e0b); }
.wk-stat-chart.fi-color-danger { color: var(--danger-500, #ef4444); }
.wk-stat-chart.fi-color-primary { color: var(--primary-500, #3b82f6); }
.wk-stat-spark { display: block; width: 100%; height: 1.45rem; }
.wk-stat-spark-line {
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    opacity: 0.9;
}
.wk-stat-spark-area { stroke: none; fill: currentColor; opacity: 0.12; }
.wk-stat-chart-action {
    position: absolute;
    right: -0.15rem;
    bottom: -0.2rem;
    z-index: 1;
}
.wk-stat-link { margin: 0; }
</style>
