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
