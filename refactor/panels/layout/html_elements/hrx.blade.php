@once
@assets
<style>
    .webkernel-hrx-wrap {
        display: block;
        width: calc(100% + 2 * var(--hrx-bleed));
        overflow: hidden;
        height: 1px;
        padding: 0;
        box-sizing: border-box;
    }

    .webkernel-hrx {
        display: block;
        border: 0;
        height: 1px;
        margin: 0;
        padding: 0;
        width: 100%;
        pointer-events: none;
        background-color: color-mix(in oklab, var(--gray-950, #0a0a0a) 12%, transparent);
    }

    .dark .webkernel-hrx {
        background-color: color-mix(in oklab, var(--color-white, #ffffff) 10%, transparent);
    }
</style>
@endassets
@endonce

<span class="webkernel-hrx-wrap" aria-hidden="true">
    <hr {{ $attributes->class(['webkernel-hrx']) }} />
</span>
