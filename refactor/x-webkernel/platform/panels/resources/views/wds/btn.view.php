@once('wds.btn')
@push('styles')
<style>
.wds-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.4375rem 0.875rem;
  font-size: var(--wds-text-sm);
  font-weight: 500;
  border-radius: var(--wds-radius);
  border: 1px solid transparent;
  cursor: pointer;
  transition: background var(--wds-transition), color var(--wds-transition), border-color var(--wds-transition), box-shadow var(--wds-transition);
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
.wds-btn.wds-color-primary { background: var(--primary-600); color: #fff; }
.wds-btn.wds-color-primary:hover { background: var(--primary-700); }
.wds-btn.wds-color-danger { background: var(--danger-600); color: #fff; }
.wds-btn.wds-color-danger:hover { filter: brightness(0.92); }
.wds-btn.wds-color-success { background: var(--success-600); color: #fff; }
.wds-btn.wds-color-warning { background: var(--warning-600); color: #fff; }
.wds-btn.wds-color-info { background: var(--info-600); color: #fff; }
.wds-btn.wds-color-gray { background: var(--wds-bg-subtle); color: var(--wds-text); border-color: var(--wds-border); }
.wds-btn.wds-color-gray:hover { background: var(--wds-border); }
.wds-btn.wds-outlined {
  background: var(--wds-surface);
  border-color: var(--wds-border);
  color: var(--wds-text);
}
.wds-btn.wds-outlined:hover { background: var(--wds-bg-subtle); border-color: var(--wds-border-strong); }
.wds-btn.wds-outlined.wds-color-primary { background: transparent; color: var(--primary-700); border-color: var(--primary-600); }
.wds-btn.wds-outlined.wds-color-danger { background: transparent; color: var(--danger-700); border-color: var(--danger-600); }
[data-wds-theme="dark"] .wds-btn.wds-outlined.wds-color-primary { color: var(--primary-300); border-color: var(--primary-400); }
[data-wds-theme="dark"] .wds-btn.wds-outlined.wds-color-danger { color: var(--danger-300); border-color: var(--danger-400); }
.wds-btn.wds-ghost { background: transparent; color: var(--wds-text-muted); }
.wds-btn.wds-ghost:hover { background: var(--wds-bg-subtle); color: var(--wds-text); }
.wds-btn.wds-size-xs { padding: 0.125rem 0.5rem; font-size: var(--wds-text-xs); gap: 0.25rem; }
.wds-btn.wds-size-sm { padding: 0.25rem 0.625rem; font-size: var(--wds-text-xs); }
.wds-btn.wds-size-md { padding: 0.4375rem 0.875rem; }
.wds-btn.wds-size-lg { padding: 0.625rem 1.125rem; font-size: var(--wds-text-base); }
.wds-btn.wds-size-xl { padding: 0.75rem 1.375rem; font-size: var(--wds-text-md); }
.wds-btn.wds-disabled,
.wds-btn[disabled],
.wds-btn[aria-disabled="true"] { opacity: 0.55; pointer-events: none; cursor: not-allowed; }
a.wds-btn { text-decoration: none; color: inherit; }
.wds-btn-label { display: inline-flex; align-items: center; }
.wds-btn-badge { margin-inline-start: 0.25rem; }
.wds-btn-group { display: inline-flex; align-items: stretch; }
.wds-btn-group > .wds-btn { border-radius: 0; }
.wds-btn-group > .wds-btn:first-child { border-top-left-radius: var(--wds-radius); border-bottom-left-radius: var(--wds-radius); }
.wds-btn-group > .wds-btn:last-child { border-top-right-radius: var(--wds-radius); border-bottom-right-radius: var(--wds-radius); }
.wds-btn-group > .wds-btn + .wds-btn { margin-inline-start: -1px; }

.wds-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.125rem 0.5rem;
  border-radius: var(--wds-radius-full);
  font-size: var(--wds-text-xs);
  font-weight: 600;
}
.wds-badge.wds-color-success { background: var(--success-50); color: var(--success-700); }
.wds-badge.wds-color-warning { background: var(--warning-50); color: var(--warning-700); }
.wds-badge.wds-color-danger { background: var(--danger-50); color: var(--danger-700); }
.wds-badge.wds-color-info { background: var(--info-50); color: var(--info-700); }
.wds-badge.wds-color-primary { background: var(--primary-50); color: var(--primary-700); }
.wds-badge.wds-color-gray { background: var(--wds-bg-subtle); color: var(--wds-text-muted); }
</style>
@endpush
@endonce
