{{-- webkernel::panels.layout._modal — draggable modals (private partial) --}}

{{-- Base CSS positioning fix for all users (guests + auth) --}}
<style>
    [role="dialog"],
    .fi-modal {
        position: fixed !important;
        inset: 0 !important;
    }
</style>

@if(webapp()->auth()->check())
<style>
    .fi-modal-window {
        transition: none !important;
    }
    .fi-modal-header,
    .fi-modal-heading,
    [data-slot="title"] {
        cursor: move !important;
    }
    .fi-modal-header button,
    .fi-modal-header a,
    .fi-modal-header input {
        cursor: pointer !important;
    }
</style>

<script>
(function () {
    'use strict';

    const modalSelectors = ['.fi-modal-window', '[role="dialog"]'];
    const headerSelectors = ['.fi-modal-header', '.fi-modal-heading', '[data-slot="title"]', 'header'];

    function makeDraggable(modal) {
        if (!modal || modal.dataset.draggableModalAttached === '1') return;

        // Move modal root to body to prevent transform context breaks on scroll
        const rootModal = modal.closest('[role="dialog"], .fi-modal') || modal;
        if (rootModal && rootModal.parentElement && rootModal.parentElement !== document.body) {
            document.body.appendChild(rootModal);
        }

        const win = modal.classList.contains('fi-modal-window')
            ? modal
            : (modal.querySelector('.fi-modal-window') || modal);

        if (!win) return;

        modal.dataset.draggableModalAttached = '1';

        let handle = null;
        for (const sel of headerSelectors) {
            handle = win.querySelector(sel);
            if (handle) break;
        }
        if (!handle) handle = win;

        handle.style.cursor     = 'move';
        handle.style.userSelect = 'none';

        let startX = 0, startY = 0, initialX = 0, initialY = 0;

        function onMouseDown(e) {
            if (e.button !== 0) return;
            if (e.target.closest('button, input, select, textarea, a')) return;

            const rect = win.getBoundingClientRect();
            initialX = rect.left;
            initialY = rect.top;
            startX   = e.clientX;
            startY   = e.clientY;

            win.style.width     = rect.width + 'px';
            win.style.position  = 'fixed';
            win.style.margin    = '0';
            win.style.transform = 'none';
            win.style.removeProperty('inset');
            win.style.left   = initialX + 'px';
            win.style.top    = initialY + 'px';
            win.style.right  = 'auto';
            win.style.bottom = 'auto';

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup',   onMouseUp);
            e.preventDefault();
        }

        function onMouseMove(e) {
            win.style.left = (initialX + (e.clientX - startX)) + 'px';
            win.style.top  = (initialY + (e.clientY - startY)) + 'px';
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup',   onMouseUp);
        }

        handle.addEventListener('mousedown', onMouseDown);
    }

    const observer = new MutationObserver(mutations => {
        for (const m of mutations) {
            for (const node of m.addedNodes) {
                if (!(node instanceof HTMLElement)) continue;
                if (modalSelectors.some(s => node.matches(s))) {
                    setTimeout(() => makeDraggable(node), 100);
                } else {
                    const found = node.querySelector(modalSelectors.join(','));
                    if (found) setTimeout(() => makeDraggable(found), 100);
                }
            }
        }
    });

    function init() {
        document.querySelectorAll(modalSelectors.join(',')).forEach(makeDraggable);
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endif
