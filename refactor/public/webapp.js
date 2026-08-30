/*
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//>
//> Generated. Do not edit.
//> Package JS. Source: html-attributes, colocated view JS, resources/js.
*/

(function () {
  var node = null;
  function el() {
    if (node) return node;
    node = document.createElement('div');
    node.className = 'w-tooltip';
    node.setAttribute('role', 'tooltip');
    document.body.appendChild(node);
    return node;
  }
  function place(anchor, tip, where) {
    var r = anchor.getBoundingClientRect();
    var t = tip.getBoundingClientRect();
    var x = r.left + (r.width - t.width) / 2;
    var y = r.top - t.height - 8;
    if (where === 'bottom') y = r.bottom + 8;
    if (where === 'left') { x = r.left - t.width - 8; y = r.top + (r.height - t.height) / 2; }
    if (where === 'right') { x = r.right + 8; y = r.top + (r.height - t.height) / 2; }
    x = Math.max(8, Math.min(x, window.innerWidth - t.width - 8));
    y = Math.max(8, Math.min(y, window.innerHeight - t.height - 8));
    tip.style.left = x + 'px';
    tip.style.top = y + 'px';
  }
  function hide() {
    if (!node) return;
    node.classList.remove('w-open');
  }
  function show(anchor) {
    var text = anchor.getAttribute('x-tooltip');
    if (!text) return;
    var tip = el();
    tip.textContent = text;
    tip.classList.add('w-open');
    place(anchor, tip, anchor.getAttribute('x-tooltip-placement') || 'top');
  }
  document.addEventListener('pointerover', function (e) {
    var a = e.target.closest('[x-tooltip]');
    if (a) show(a);
  });
  document.addEventListener('pointerout', function (e) {
    var a = e.target.closest('[x-tooltip]');
    if (a && (!e.relatedTarget || !a.contains(e.relatedTarget))) hide();
  });
  document.addEventListener('focusin', function (e) {
    var a = e.target.closest('[x-tooltip]');
    if (a) show(a);
  });
  document.addEventListener('focusout', hide);
})();
(function () {
  document.addEventListener('click', function (event) {
    document.querySelectorAll('[data-w-dropdown]').forEach(function (box) {
      if (box.contains(event.target)) {
        if (event.target.closest('[data-w-dropdown-trigger]')) {
          box.classList.toggle('w-open');
        }
        return;
      }
      box.classList.remove('w-open');
    });
  });
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-w-dropdown].w-open').forEach(function (box) {
      box.classList.remove('w-open');
    });
  });
})();
(function () {
  document.addEventListener('click', function (event) {
    var closer = event.target.closest('[data-w-modal-close], [data-w-modal-overlay]');
    if (closer) {
      var modal = closer.closest('[data-w-modal]');
      if (modal) modal.classList.remove('w-open');
    }
    var trigger = event.target.closest('[data-w-modal-trigger]');
    if (trigger) {
      var opened = trigger.closest('[data-w-modal]');
      if (opened) opened.classList.add('w-open');
    }
  });
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-w-modal].w-open').forEach(function (modal) {
      modal.classList.remove('w-open');
    });
  });
})();
document.addEventListener('click', function (event) {
  var tab = event.target.closest('[data-w-tabs] [role="tab"]');
  if (!tab) return;
  var root = tab.closest('[data-w-tabs]');
  var id = tab.getAttribute('data-tab');
  root.querySelectorAll('[role="tab"]').forEach(function (other) {
    var on = other === tab;
    other.classList.toggle('w-active', on);
    other.setAttribute('aria-selected', on ? 'true' : 'false');
  });
  root.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
    panel.hidden = panel.getAttribute('data-tab-panel') !== id;
  });
});
document.addEventListener('click', function (event) {
  var btn = event.target.closest('[data-w-toggle]');
  if (!btn) return;
  var on = btn.getAttribute('aria-checked') !== 'true';
  btn.setAttribute('aria-checked', on ? 'true' : 'false');
  var wrap = btn.parentElement;
  var input = wrap ? wrap.querySelector('[data-w-toggle-input]') : null;
  if (input) input.checked = on;
});
(function () {
  document.addEventListener('click', function (event) {
    document.querySelectorAll('[data-w-lang]').forEach(function (box) {
      if (!box.contains(event.target)) box.classList.remove('w-open');
    });
  });
})();
(function () {
  window.toggleUserMenu = function () {
    var el = document.getElementById('w-user-menu');
    if (el) el.classList.toggle('w-open');
  };
  document.addEventListener('click', function (event) {
    var el = document.getElementById('w-user-menu');
    if (el && !el.contains(event.target)) {
      el.classList.remove('w-open');
    }
  });
})();
(function () {
  document.body.addEventListener('htmx:configRequest', function (event) {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && event.detail && event.detail.headers) {
      event.detail.headers['X-CSRF-TOKEN'] = meta.getAttribute('content') || '';
    }
  });
})();
