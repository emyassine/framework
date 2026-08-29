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

  function setLayout(layout) {
    document.documentElement.dataset.wdsLayout = layout;
    ['sidebar', 'topnav', 'horizontal'].forEach(id => {
      const btn = document.getElementById('btn-layout-' + id);
      if (btn) btn.classList.toggle('wds-active', id === layout);
    });
    if (layout !== 'sidebar') {
      document.documentElement.dataset.wdsSidebar = 'expanded';
    }
    localStorage.setItem('wds-layout', layout);
  }

  (function restoreState() {
    const savedTheme = localStorage.getItem('wds-theme');
    const savedLayout = localStorage.getItem('wds-layout');
    const savedSidebar = localStorage.getItem('wds-sidebar');

    if (savedTheme) {
      document.documentElement.dataset.wdsTheme = savedTheme;
      if (savedTheme === 'dark') {
        const sun = document.getElementById('icon-sun');
        const moon = document.getElementById('icon-moon');
        if (sun) sun.style.display = 'none';
        if (moon) moon.style.display = 'block';
      }
    }
    if (savedLayout) setLayout(savedLayout);
    if (savedSidebar) document.documentElement.dataset.wdsSidebar = savedSidebar;
  })();

  function toggleUserMenu() {
    const el = document.getElementById('wds-user-menu');
    if (el) el.classList.toggle('wds-open');
  }
  document.addEventListener('click', function (event) {
    const el = document.getElementById('wds-user-menu');
    if (!el || el.contains(event.target)) {
      return;
    }
    el.classList.remove('wds-open');
  });
</script>
