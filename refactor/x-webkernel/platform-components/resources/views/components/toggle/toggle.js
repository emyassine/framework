document.addEventListener('click', function (event) {
  var btn = event.target.closest('[data-w-toggle]');
  if (!btn) return;
  var on = btn.getAttribute('aria-checked') !== 'true';
  btn.setAttribute('aria-checked', on ? 'true' : 'false');
  var wrap = btn.parentElement;
  var input = wrap ? wrap.querySelector('[data-w-toggle-input]') : null;
  if (input) input.checked = on;
});
