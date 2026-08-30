<style>
/* ── webkernel::images.triptych ── */
.wk-triptych {
    display: flex;
    align-items: center;
    justify-content: center;
    width: var(--wk-triptych-width, 100%);
    height: var(--wk-triptych-height, 220px);
    overflow: hidden;
    position: relative;
    box-sizing: border-box;
}
.wk-triptych__side {
    width: var(--wk-triptych-side-width, 20.5%);
    height: var(--wk-triptych-side-height, 84%);
    flex-shrink: 0;
    overflow: hidden;
}
.wk-triptych__side--left {
    border-top-left-radius: var(--radius-lg) !important;
    border-bottom-left-radius: var(--radius-lg) !important;
}
.wk-triptych__side--right {
    border-top-right-radius: var(--radius-lg) !important;
    border-bottom-right-radius: var(--radius-lg) !important;
}
.wk-triptych__center {
    width: var(--wk-triptych-center-width, 59%);
    height: 100%;
    flex-shrink: 0;
    padding: 0 4px;
    box-sizing: border-box;
    overflow: hidden;
}
.wk-triptych__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.wk-triptych__center .wk-triptych__img {
    border-radius: var(--radius-lg) !important;
}

</style>
