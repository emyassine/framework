<div class="webkernel-shell-nav-section-title">Platform</div>
<div class="webkernel-shell-nav-group">
  <a href="/system" class="webkernel-shell-nav-item{{ ($current ?? '') === 'dashboard' ? ' active' : '' }}">
    <x-webkernel::icon name="layout-dashboard" />
    <span class="webkernel-shell-nav-label">Dashboard</span>
  </a>
</div>

<div class="webkernel-shell-nav-section-title">Modules</div>
<div class="webkernel-shell-nav-group">
  <a href="/billing/invoices" class="webkernel-shell-nav-item{{ ($current ?? '') === 'invoices' ? ' active' : '' }}">
    <x-webkernel::icon name="receipt" />
    <span class="webkernel-shell-nav-label">Invoices</span>
  </a>
</div>
