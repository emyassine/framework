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
    if (!el || el.contains(event.target)) {
      return;
    }
    el.classList.remove('wds-open');
  });
</script>
