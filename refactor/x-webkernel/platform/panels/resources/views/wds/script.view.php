<script>
  function toggleTheme() {
    const html = document.documentElement;
    const next = html.dataset.wdsTheme === 'dark' ? 'light' : 'dark';
    html.dataset.wdsTheme = next;
    const sun = document.getElementById('icon-sun');
    const moon = document.getElementById('icon-moon');
    if (sun) sun.style.display = next === 'dark' ? 'none' : 'block';
    if (moon) moon.style.display = next === 'dark' ? 'block' : 'none';
    localStorage.setItem('wds-theme', next);
  }

  function toggleSidebar() {
    const html = document.documentElement;
    html.dataset.wdsSidebar = html.dataset.wdsSidebar === 'collapsed' ? 'expanded' : 'collapsed';
    localStorage.setItem('wds-sidebar', html.dataset.wdsSidebar);
  }

  (function syncChrome() {
    if (document.documentElement.dataset.wdsTheme === 'dark') {
      const sun = document.getElementById('icon-sun');
      const moon = document.getElementById('icon-moon');
      if (sun) sun.style.display = 'none';
      if (moon) moon.style.display = 'block';
    }
  })();

  function toggleUserMenu() {
    const el = document.getElementById('wds-user-menu');
    if (el) el.classList.toggle('wds-open');
  }
  document.addEventListener('click', function (event) {
    const el = document.getElementById('wds-user-menu');
    if (el && !el.contains(event.target)) {
      el.classList.remove('wds-open');
    }
    document.querySelectorAll('[data-wds-lang]').forEach(function (box) {
      if (!box.contains(event.target)) box.classList.remove('wds-open');
    });
    document.querySelectorAll('[data-wds-dropdown]').forEach(function (box) {
      if (box.contains(event.target)) {
        if (event.target.closest('[data-wds-dropdown-trigger]')) {
          box.classList.toggle('wds-open');
        }
        return;
      }
      box.classList.remove('wds-open');
    });
    const modalClose = event.target.closest('[data-wds-modal-close], [data-wds-modal-overlay]');
    if (modalClose) {
      const modal = modalClose.closest('[data-wds-modal]');
      if (modal) modal.classList.remove('wds-open');
    }
    const modalTrigger = event.target.closest('[data-wds-modal-trigger]');
    if (modalTrigger) {
      const modal = modalTrigger.closest('[data-wds-modal]');
      if (modal) modal.classList.add('wds-open');
    }
  });
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-wds-modal].wds-open').forEach(function (modal) {
      modal.classList.remove('wds-open');
    });
    document.querySelectorAll('[data-wds-dropdown].wds-open').forEach(function (box) {
      box.classList.remove('wds-open');
    });
  });

  document.querySelectorAll('[data-wds-tabs]').forEach(function (root) {
    root.querySelectorAll('[role="tab"]').forEach(function (tab) {
      tab.addEventListener('click', function () {
        const id = tab.getAttribute('data-tab');
        root.querySelectorAll('[role="tab"]').forEach(function (other) {
          const on = other === tab;
          other.classList.toggle('wds-active', on);
          other.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        root.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
          panel.hidden = panel.getAttribute('data-tab-panel') !== id;
        });
      });
    });
  });

  document.querySelectorAll('[data-wds-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const on = btn.getAttribute('aria-checked') !== 'true';
      btn.setAttribute('aria-checked', on ? 'true' : 'false');
      const wrap = btn.parentElement;
      const input = wrap ? wrap.querySelector('[data-wds-toggle-input]') : null;
      if (input) input.checked = on;
    });
  });
</script>
