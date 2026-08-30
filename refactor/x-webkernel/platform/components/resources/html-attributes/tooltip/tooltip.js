(function () {
  var node = null;
  function el() {
    if (node) return node;
    node = document.createElement('div');
    node.className = 'w-tooltip';
    node.setAttribute('role', 'tooltip');
    document.body.appendChild(node);
    return node;
  }
  function place(anchor, tip, where) {
    var r = anchor.getBoundingClientRect();
    var t = tip.getBoundingClientRect();
    var x = r.left + (r.width - t.width) / 2;
    var y = r.top - t.height - 8;
    if (where === 'bottom') y = r.bottom + 8;
    if (where === 'left') { x = r.left - t.width - 8; y = r.top + (r.height - t.height) / 2; }
    if (where === 'right') { x = r.right + 8; y = r.top + (r.height - t.height) / 2; }
    x = Math.max(8, Math.min(x, window.innerWidth - t.width - 8));
    y = Math.max(8, Math.min(y, window.innerHeight - t.height - 8));
    tip.style.left = x + 'px';
    tip.style.top = y + 'px';
  }
  function hide() {
    if (!node) return;
    node.classList.remove('w-open');
  }
  function show(anchor) {
    var text = anchor.getAttribute('x-tooltip');
    if (!text) return;
    var tip = el();
    tip.textContent = text;
    tip.classList.add('w-open');
    place(anchor, tip, anchor.getAttribute('x-tooltip-placement') || 'top');
  }
  document.addEventListener('pointerover', function (e) {
    var a = e.target.closest('[x-tooltip]');
    if (a) show(a);
  });
  document.addEventListener('pointerout', function (e) {
    var a = e.target.closest('[x-tooltip]');
    if (a && (!e.relatedTarget || !a.contains(e.relatedTarget))) hide();
  });
  document.addEventListener('focusin', function (e) {
    var a = e.target.closest('[x-tooltip]');
    if (a) show(a);
  });
  document.addEventListener('focusout', hide);
})();
