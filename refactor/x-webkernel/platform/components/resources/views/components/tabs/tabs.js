(function () {
  function activate(root, id) {
    root.querySelectorAll(':scope > .w-tabs [role="tab"]').forEach(function (tab) {
      var on = tab.getAttribute('data-tab') === id;
      tab.classList.toggle('w-active', on);
      tab.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    root.querySelectorAll(':scope > [data-tab-panel]').forEach(function (panel) {
      panel.classList.toggle('w-active', panel.getAttribute('data-tab-panel') === id);
    });
    var persistId = root.getAttribute('data-persist-tab');
    if (persistId) {
      try { localStorage.setItem('w-tabs:' + persistId, id); } catch (e) {}
    }
    var queryKey = root.getAttribute('data-persist-query');
    if (queryKey) {
      var url = new URL(window.location.href);
      url.searchParams.set(queryKey, id);
      history.replaceState(null, document.title, url.toString());
    }
    var overflow = root.querySelector('[data-w-tabs-overflow-list]');
    if (overflow) {
      overflow.querySelectorAll('[data-tab]').forEach(function (item) {
        item.classList.toggle('w-selected', item.getAttribute('data-tab') === id);
      });
    }
  }

  function restore(root) {
    var queryKey = root.getAttribute('data-persist-query');
    var persistId = root.getAttribute('data-persist-tab');
    var chosen = null;
    if (queryKey) {
      chosen = new URLSearchParams(window.location.search).get(queryKey);
    }
    if (!chosen && persistId) {
      try { chosen = localStorage.getItem('w-tabs:' + persistId); } catch (e) {}
    }
    if (!chosen) return;
    if (!root.querySelector('[data-tab="' + CSS.escape(chosen) + '"]')) return;
    activate(root, chosen);
  }

  function updateOverflow(root) {
    if (root.getAttribute('data-scrollable') !== 'false') return;
    var nav = root.querySelector(':scope > .w-tabs');
    var overflow = root.querySelector('[data-w-tabs-overflow]');
    var list = root.querySelector('[data-w-tabs-overflow-list]');
    if (!nav || !overflow || !list) return;
    var tabs = Array.prototype.slice.call(nav.querySelectorAll(':scope > .w-tabs-item[data-tab]'));
    tabs.forEach(function (tab) { tab.hidden = false; });
    overflow.hidden = true;
    list.replaceChildren();
    if (nav.scrollWidth <= nav.clientWidth) return;
    overflow.hidden = false;
    var more = overflow.querySelector('[data-w-tabs-more]');
    var moreWidth = more ? more.getBoundingClientRect().width : 40;
    var available = nav.clientWidth - moreWidth - 8;
    var used = 0;
    var overflowed = false;
    tabs.forEach(function (tab) {
      used += tab.getBoundingClientRect().width + 4;
      if (overflowed || used > available) {
        overflowed = true;
        tab.hidden = true;
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'w-dropdown-list-item';
        item.setAttribute('data-tab', tab.getAttribute('data-tab') || '');
        item.setAttribute('role', 'menuitem');
        if (tab.classList.contains('w-active')) item.classList.add('w-selected');
        var label = tab.querySelector('.w-tabs-item-label');
        item.textContent = label ? label.textContent : '';
        list.appendChild(item);
      }
    });
  }

  document.addEventListener('click', function (event) {
    var overflowItem = event.target.closest('[data-w-tabs-overflow-list] [data-tab]');
    if (overflowItem) {
      var root = overflowItem.closest('[data-w-tabs]');
      var id = overflowItem.getAttribute('data-tab');
      if (root && id) activate(root, id);
      return;
    }
    var tab = event.target.closest('[data-w-tabs] [role="tab"][data-tab]');
    if (!tab) return;
    var root = tab.closest('[data-w-tabs]');
    var id = tab.getAttribute('data-tab');
    if (!id || !root) return;
    activate(root, id);
  });

  function boot() {
    document.querySelectorAll('[data-w-tabs]').forEach(function (root) {
      restore(root);
      updateOverflow(root);
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
  window.addEventListener('resize', function () {
    document.querySelectorAll('[data-w-tabs]').forEach(updateOverflow);
  });
})();
