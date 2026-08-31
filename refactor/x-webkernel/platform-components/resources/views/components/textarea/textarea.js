(function () {
  function resize(box) {
    var field = box.querySelector('textarea');
    if (!field || box.dataset.autosize !== 'true' && !field.hasAttribute('data-autosize')) return;
    field.style.height = 'auto';
    box.style.height = field.scrollHeight + 'px';
    field.style.height = '100%';
  }
  function boot() {
    document.querySelectorAll('[data-w-textarea]').forEach(function (box) {
      var field = box.querySelector('textarea');
      if (!field) return;
      if (field.hasAttribute('data-autosize')) {
        resize(box);
        field.addEventListener('input', function () { resize(box); });
      }
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
  window.addEventListener('resize', function () {
    document.querySelectorAll('[data-w-textarea] textarea[data-autosize]').forEach(function (field) {
      var box = field.closest('[data-w-textarea]');
      if (box) resize(box);
    });
  });
})();
