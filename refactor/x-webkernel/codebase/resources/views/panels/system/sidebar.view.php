<div class="wks-nav-section-title">Platform</div>
<div class="wks-nav-group">
  <a href="/system" class="wks-nav-item{{ ($current ?? '') === 'dashboard' ? ' active' : '' }}">
    <span class="wks-nav-label">Dashboard</span>
  </a>
</div>

<div class="wks-nav-section-title">Modules</div>
<div class="wks-nav-group">
  <a href="/billing/invoices" class="wks-nav-item{{ ($current ?? '') === 'invoices' ? ' active' : '' }}">
    <span class="wks-nav-label">Invoices</span>
  </a>
</div>
