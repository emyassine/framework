(function () {
  function close(el) {
    if (!el || el.classList.contains('w-leaving')) return;
    el.classList.add('w-leaving');
    window.setTimeout(function () {
      el.remove();
      var stack = document.querySelector('.w-no');
      if (stack && !stack.querySelector('[data-w-notification]')) {
        stack.remove();
      }
    }, 300);
  }
  document.addEventListener('click', function (event) {
    var btn = event.target.closest('[data-w-notification-close]');
    if (!btn) return;
    close(btn.closest('[data-w-notification]'));
  });
  function arm(el) {
    var raw = el.getAttribute('data-duration');
    if (!raw || raw === 'persistent') return;
    var duration = parseInt(raw, 10);
    if (!duration) return;
    var timer = window.setTimeout(function () {
      if (!el.matches(':hover')) close(el);
      else el.addEventListener('mouseleave', function () { close(el); }, { once: true });
    }, duration);
    el.addEventListener('mouseenter', function () { window.clearTimeout(timer); }, { once: true });
  }
  function boot() {
    document.querySelectorAll('[data-w-notification]').forEach(arm);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
