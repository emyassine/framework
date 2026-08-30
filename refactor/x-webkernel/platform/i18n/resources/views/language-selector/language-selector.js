(function () {
  document.addEventListener('click', function (event) {
    document.querySelectorAll('[data-w-lang]').forEach(function (box) {
      if (!box.contains(event.target)) box.classList.remove('w-open');
    });
  });
})();
