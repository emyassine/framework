(function () {
  window.toggleTheme = function () {
    var html = document.documentElement;
    var next = html.dataset.wTheme === 'dark' ? 'light' : 'dark';
    html.dataset.wTheme = next;
    localStorage.setItem('w-theme', next);
  };
  window.toggleSidebar = function () {
    var html = document.documentElement;
    html.dataset.wSidebar = html.dataset.wSidebar === 'collapsed' ? 'expanded' : 'collapsed';
    localStorage.setItem('w-sidebar', html.dataset.wSidebar);
  };
})();
