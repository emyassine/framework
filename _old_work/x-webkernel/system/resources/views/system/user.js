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
