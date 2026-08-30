(function () {
  function boot(root) {
    var panels = Array.prototype.slice.call(root.querySelectorAll('[data-wizard-panel], .w-wizard-panel'));
    if (!panels.length) return;
    function show(id) {
      var i = 0;
      panels.forEach(function (panel, index) {
        var key = panel.getAttribute('data-wizard-panel') || '';
        var on = id ? key === id : index === 0;
        panel.classList.toggle('w-active', on);
        if (on) i = index;
      });
      root.querySelectorAll('[data-wizard-step]').forEach(function (item, index) {
        item.classList.toggle('w-active', id ? item.getAttribute('data-wizard-step') === id : index === 0);
      });
      var prev = root.querySelector('[data-wizard-prev]');
      var next = root.querySelector('[data-wizard-next]');
      if (prev) prev.disabled = i === 0;
      if (next) next.disabled = i === panels.length - 1;
      root.dataset.wizardIndex = String(i);
    }
    function current() {
      return parseInt(root.dataset.wizardIndex || '0', 10) || 0;
    }
    root.addEventListener('click', function (event) {
      var goto = event.target.closest('[data-wizard-goto]');
      if (goto) {
        show(goto.getAttribute('data-wizard-goto'));
        return;
      }
      if (event.target.closest('[data-wizard-next]')) {
        var n = Math.min(panels.length - 1, current() + 1);
        show(panels[n].getAttribute('data-wizard-panel') || '');
        return;
      }
      if (event.target.closest('[data-wizard-prev]')) {
        var p = Math.max(0, current() - 1);
        show(panels[p].getAttribute('data-wizard-panel') || '');
      }
    });
    show(panels[0].getAttribute('data-wizard-panel') || '');
  }
  function start() {
    document.querySelectorAll('[data-w-wizard]').forEach(boot);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
