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
