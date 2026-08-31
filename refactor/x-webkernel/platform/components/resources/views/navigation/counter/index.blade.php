<div>
  <button hx-post="{{ route('counter.increment') }}" hx-target="this" hx-swap="outerHTML">
        Increment
    </button>
    <span>{{ $count }}</span>
</div>
