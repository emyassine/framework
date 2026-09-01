/**
 * Notification component boot and lifecycle.
 * No Alpine dependency — plain DOM + CustomEvents.
 *
 * Listens for `w:notificationSent` on window and renders the notification
 * element into the `.w-no` stack already present in the page.
 *
 * Dispatches `w:notificationClosed` when a notification leaves the DOM.
 */

const ICONS = {
    success: 'check-circle',
    danger:  'x-circle',
    warning: 'alert-circle',
    info:    'info',
}

/**
 * Returns the SVG markup for a named icon.
 * Falls back to a minimal circle when the icon set is absent.
 *
 * @param {string} name
 * @returns {string}
 */
function resolveIcon(name) {
    // Webkernel icon registry hook — populated by webkernel/imagery at boot.
    if (window.WebkernelIcons && typeof window.WebkernelIcons[name] === 'string') {
        return window.WebkernelIcons[name]
    }
    // Minimal SVG fallback so layout does not break without the icon package.
    return `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>`
}

/**
 * Builds the notification DOM element from a notification data object.
 *
 * @param {{ id: string, title: string, body: string|null, status: string, icon: string|null, duration: number|string }} notification
 * @returns {HTMLElement}
 */
function buildElement(notification) {
    const status   = notification.status || 'info'
    const iconName = notification.icon   || ICONS[status] || 'info'
    const role     = status === 'danger' ? 'alert' : null

    const el = document.createElement('div')
    el.className = `w-no-notification w-status-${status} w-transition-enter-start`
    el.setAttribute('data-w-notification', '')
    el.setAttribute('data-w-notification-id', notification.id)
    el.setAttribute('data-duration', String(notification.duration ?? 6000))

    if (role) {
        el.setAttribute('role', role)
    }

    el.innerHTML = `
        <span class="w-no-notification-icon w-color-${status}" aria-hidden="true">
            ${resolveIcon(iconName)}
        </span>
        <div class="w-no-notification-main">
            <div class="w-no-notification-text">
                ${notification.title
                    ? `<h3 class="w-no-notification-title">${escapeHtml(notification.title)}</h3>`
                    : ''}
                ${notification.body
                    ? `<div class="w-no-notification-body">${escapeHtml(notification.body)}</div>`
                    : ''}
            </div>
        </div>
        <button
            type="button"
            class="w-icon-btn w-no-notification-close-btn"
            data-w-notification-close
            aria-label="Close"
            title="Close"
        >
            ${resolveIcon('x')}
        </button>
    `

    return el
}

/**
 * Minimal HTML escaping for text content inserted via innerHTML.
 *
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
}

/**
 * Removes a notification element with a leave transition.
 *
 * @param {HTMLElement} el
 * @param {string}      id
 */
function close(el, id) {
    if (!el || el.classList.contains('w-transition-leave-end')) return

    el.classList.add('w-transition-leave-end')

    const duration = parseFloat(getComputedStyle(el).transitionDuration) * 1000 || 300

    setTimeout(() => {
        el.remove()
        removeSatckWhenEmpty()
        window.dispatchEvent(
            new CustomEvent('w:notificationClosed', { detail: { id } }),
        )
    }, duration)
}

/**
 * Removes the `.w-no` stack element when it holds no more notifications.
 */
function removeSatckWhenEmpty() {
    const stack = document.querySelector('.w-no')
    if (stack && !stack.querySelector('[data-w-notification]')) {
        stack.remove()
    }
}

/**
 * Starts the auto-dismiss timer for a notification element.
 *
 * @param {HTMLElement} el
 * @param {string}      id
 */
function arm(el, id) {
    const raw = el.getAttribute('data-duration')
    if (!raw || raw === 'persistent') return

    const duration = parseInt(raw, 10)
    if (!duration) return

    let timer = setTimeout(() => {
        if (!el.matches(':hover')) {
            close(el, id)
        } else {
            el.addEventListener('mouseleave', () => close(el, id), { once: true })
        }
    }, duration)

    el.addEventListener('mouseenter', () => clearTimeout(timer), { once: true })
}

/**
 * Resolves or creates the `.w-no` stack container.
 *
 * @returns {HTMLElement}
 */
function resolveStack() {
    let stack = document.querySelector('.w-no')
    if (!stack) {
        stack = document.createElement('div')
        stack.className = 'w-no w-align-end w-vertical-align-start'
        stack.setAttribute('role', 'status')
        stack.setAttribute('aria-atomic', 'false')
        document.body.appendChild(stack)
    }
    return stack
}

/**
 * Renders a notification and appends it to the stack.
 *
 * @param {{ id: string, title: string, body: string|null, status: string, icon: string|null, duration: number|string }} notification
 */
function render(notification) {
    const stack = resolveStack()
    const el    = buildElement(notification)

    stack.appendChild(el)

    // Trigger enter transition on next frame.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            el.classList.remove('w-transition-enter-start')
        })
    })

    arm(el, notification.id)
}

/**
 * Boots the notification component.
 * Arms existing server-rendered notifications and listens for JS-sent ones.
 */
function boot() {
    // Arm server-rendered notifications already in the DOM.
    document.querySelectorAll('[data-w-notification]').forEach((el) => {
        const id = el.getAttribute('data-w-notification-id') ?? ''
        arm(el, id)
    })

    // Handle close button clicks (delegated).
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-w-notification-close]')
        if (!btn) return
        const el = btn.closest('[data-w-notification]')
        if (!el) return
        const id = el.getAttribute('data-w-notification-id') ?? ''
        close(el, id)
    })

    // Handle JS-sent notifications.
    window.addEventListener('w:notificationSent', (event) => {
        render(event.detail.notification)
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot)
} else {
    boot()
}

export { render, close, boot }
