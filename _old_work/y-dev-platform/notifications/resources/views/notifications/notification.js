class Notification {
    constructor() {
        // `crypto.randomUUID()` requires a secure context (HTTPS); fall back to
        // `crypto.getRandomValues()` which works in all contexts including HTTP.
        this.id(
            crypto.randomUUID?.() ??
                '10000000-1000-4000-8000-100000000000'.replace(/[018]/g, (c) =>
                    (
                        +c ^
                        (crypto.getRandomValues(new Uint8Array(1))[0] &
                            (15 >> (+c / 4)))
                    ).toString(16),
                ),
        )
        return this
    }

    id(id) {
        this._id = id
        return this
    }

    title(title) {
        this._title = title
        return this
    }

    body(body) {
        this._body = body
        return this
    }

    status(status) {
        this._status = status
        return this
    }

    icon(icon) {
        this._icon = icon
        return this
    }

    duration(duration) {
        this._duration = duration
        return this
    }

    seconds(seconds) {
        this.duration(seconds * 1000)
        return this
    }

    persistent() {
        this.duration('persistent')
        return this
    }

    danger() {
        this.status('danger')
        return this
    }

    info() {
        this.status('info')
        return this
    }

    success() {
        this.status('success')
        return this
    }

    warning() {
        this.status('warning')
        return this
    }

    toJSON() {
        return {
            id:       this._id,
            title:    this._title,
            body:     this._body,
            status:   this._status,
            icon:     this._icon,
            duration: this._duration,
        }
    }

    send() {
        window.dispatchEvent(
            new CustomEvent('w:notificationSent', {
                detail: { notification: this.toJSON() },
            }),
        )
        return this
    }
}

export { Notification }
