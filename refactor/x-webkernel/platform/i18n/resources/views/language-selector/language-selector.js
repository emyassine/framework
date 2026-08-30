(function () {
  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('.w-lang-trigger');
    var box = event.target.closest('[data-w-lang]');
    document.querySelectorAll('[data-w-lang]').forEach(function (el) {
      if (el !== box) el.classList.remove('w-open');
    });
    if (trigger && box) {
      box.classList.toggle('w-open');
    }
  });
})();
