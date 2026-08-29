<div class="wds-sidebar-group-label">Platform</div>
<div class="wds-sidebar-group">
  <a href="/system" class="wds-sidebar-item{{ ($current ?? '') === 'dashboard' ? ' wds-active' : '' }}">
    <span class="wds-icon">{!! icon('layout-dashboard', 'wds-icon-svg') !!}</span>
    <span class="wds-sidebar-item-label">Dashboard</span>
  </a>
</div>

<div class="wds-sidebar-group-label">Modules</div>
<div class="wds-sidebar-group">
  <a href="/billing/invoices" class="wds-sidebar-item{{ ($current ?? '') === 'invoices' ? ' wds-active' : '' }}">
    <span class="wds-icon">{!! icon('receipt', 'wds-icon-svg') !!}</span>
    <span class="wds-sidebar-item-label">Invoices</span>
  </a>
</div>
