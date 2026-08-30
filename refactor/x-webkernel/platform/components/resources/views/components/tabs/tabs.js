document.addEventListener('click', function (event) {
  var tab = event.target.closest('[data-w-tabs] [role="tab"]');
  if (!tab) return;
  var root = tab.closest('[data-w-tabs]');
  var id = tab.getAttribute('data-tab');
  root.querySelectorAll('[role="tab"]').forEach(function (other) {
    var on = other === tab;
    other.classList.toggle('w-active', on);
    other.setAttribute('aria-selected', on ? 'true' : 'false');
  });
  root.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
    panel.hidden = panel.getAttribute('data-tab-panel') !== id;
  });
});
