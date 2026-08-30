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
