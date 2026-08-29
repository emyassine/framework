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

  (function syncChrome() {
    const layout = document.documentElement.dataset.wdsLayout;
    ['sidebar', 'topnav', 'horizontal'].forEach(function (id) {
      const btn = document.getElementById('btn-layout-' + id);
      if (btn) btn.classList.toggle('wds-active', id === layout);
    });
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

  function applyAppOrder() {
    const list = document.getElementById('wds-app-list');
    if (!list) {
      return;
    }
    const raw = localStorage.getItem('wds-app-order');
    if (!raw) {
      return;
    }
    let order;
    try {
      order = JSON.parse(raw);
    } catch (e) {
      return;
    }
    if (!Array.isArray(order)) {
      return;
    }
    const logo = document.getElementById('wds-app-logo');
    order.forEach(function (id) {
      const item = list.querySelector('.wds-app-item[data-app="' + id + '"]');
      if (item) {
        list.appendChild(item);
      }
    });
    if (logo) {
      list.insertBefore(logo, list.firstChild);
    }
  }

  function persistAppOrder() {
    const list = document.getElementById('wds-app-list');
    if (!list) {
      return;
    }
    const ids = [];
    list.querySelectorAll('.wds-app-item[data-app]').forEach(function (item) {
      ids.push(item.getAttribute('data-app'));
    });
    localStorage.setItem('wds-app-order', JSON.stringify(ids));
  }

  function toggleAppReorder() {
    const list = document.getElementById('wds-app-list');
    const btn = document.getElementById('wds-app-reorder');
    if (!list || list.dataset.customizable !== '1') {
      return;
    }
    const on = !list.classList.contains('wds-reordering');
    list.classList.toggle('wds-reordering', on);
    if (btn) btn.classList.toggle('wds-active', on);
    list.querySelectorAll('.wds-app-item').forEach(function (item) {
      item.setAttribute('draggable', on ? 'true' : 'false');
    });
  }

  (function initAppReorder() {
    const list = document.getElementById('wds-app-list');
    if (!list || list.dataset.customizable !== '1') {
      return;
    }
    applyAppOrder();
    let dragging = null;
    list.addEventListener('dragstart', function (event) {
      const item = event.target.closest('.wds-app-item');
      if (!item || !list.classList.contains('wds-reordering')) {
        event.preventDefault();
        return;
      }
      dragging = item;
      event.dataTransfer.effectAllowed = 'move';
    });
    list.addEventListener('dragover', function (event) {
      if (!dragging) {
        return;
      }
      event.preventDefault();
      const over = event.target.closest('.wds-app-item');
      if (!over || over === dragging) {
        return;
      }
      const rect = over.getBoundingClientRect();
      const before = event.clientY < rect.top + rect.height / 2;
      list.insertBefore(dragging, before ? over : over.nextSibling);
    });
    list.addEventListener('drop', function (event) {
      event.preventDefault();
      persistAppOrder();
      dragging = null;
    });
    list.addEventListener('dragend', function () {
      persistAppOrder();
      dragging = null;
    });
  })();
</script>
