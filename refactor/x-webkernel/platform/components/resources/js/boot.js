(function () {
  document.body.addEventListener('htmx:configRequest', function (event) {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && event.detail && event.detail.headers) {
      event.detail.headers['X-CSRF-TOKEN'] = meta.getAttribute('content') || '';
    }
  });
})();
