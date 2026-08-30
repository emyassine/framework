{{--
  utility-classes/_fi-stretch
  ───────────────────────────
  Global equal-height helpers for Filament surfaces inside CSS grid / flex.

  Problem
  ───────
  Grid/flex stretch the *cell* (e.g. <li>), but Filament wrappers
  (.fi-section → .fi-section-header / .fi-section-content-ctn → .fi-section-content)
  do not fill that cell unless the height chain is explicit. Header-only
  sections (heading + description, empty slot) are especially uneven across
  a @foreach because heights stay content-driven.

  Opt-in API (use either or both)
  ──────────────────────────────
  A) On each Filament section:
       <x-filament::section class="fi-stretch" …>

  B) On the grid/flex container (stretches every direct child card):
       <ul class="your__grid fi-grid-equal-rows">
         <li><x-filament::section …></li>
       </ul>

  Variants (with .fi-stretch)
  ──────────────────────────
  fi-stretch       fill cell height
  fi-stretch--col  content stack is flex column
  fi-stretch--end  push content to bottom
--}}
<style id="webkernel-utility-fi-stretch">
/* ── A) Opt-in on the Filament section ─────────────────────────────── */

.fi-stretch {
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    min-height: 100% !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}

.fi-stretch > .fi-section-header {
    flex: 0 0 auto !important;
    height: auto !important;
    min-height: 0 !important;
    align-items: flex-start !important;
}

.fi-stretch > .fi-section-content-ctn {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    height: auto !important;
    box-sizing: border-box !important;
}

.fi-stretch > .fi-section-content-ctn > .fi-section-content {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    height: 100% !important;
    box-sizing: border-box !important;
}

.fi-stretch > .fi-section-footer {
    flex: 0 0 auto !important;
}

.fi-stretch--col > .fi-section-content-ctn,
.fi-stretch--col > .fi-section-content-ctn > .fi-section-content {
    display: flex !important;
    flex-direction: column !important;
}

.fi-stretch--end > .fi-section-content-ctn > .fi-section-content {
    display: flex !important;
    flex-direction: column !important;
    justify-content: flex-end !important;
}

/* ── B) Opt-in on the parent grid / flex container ─────────────────── */
/*
 *   <ul class="… fi-grid-equal-rows">
 *     <li><x-filament::section class="fi-stretch">…</x-filament::section></li>
 *   </ul>
 *
 * Uses flex on each cell so the Filament root fills the stretched row height
 * without relying on percentage height of the grid item itself.
 */

.fi-grid-equal-rows {
    align-items: stretch !important;
}

/* Direct cell (usually <li> or .grid-item) */
.fi-grid-equal-rows > * {
    display: flex !important;
    flex-direction: column !important;
    min-width: 0 !important;
    min-height: 0 !important;
    align-self: stretch !important;
}

/* Filament section as direct child of the cell */
.fi-grid-equal-rows > * > .fi-section {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    width: 100% !important;
    min-height: 100% !important;
    height: 100% !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}

.fi-grid-equal-rows > * > .fi-section > .fi-section-header {
    flex: 0 0 auto !important;
    height: auto !important;
    min-height: 0 !important;
    align-items: flex-start !important;
}

.fi-grid-equal-rows > * > .fi-section > .fi-section-content-ctn {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    box-sizing: border-box !important;
}

.fi-grid-equal-rows > * > .fi-section > .fi-section-content-ctn > .fi-section-content {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    height: 100% !important;
    box-sizing: border-box !important;
}

.fi-grid-equal-rows > * > .fi-section > .fi-section-footer {
    flex: 0 0 auto !important;
}

/* Section is a direct child of the equal-rows container (no <li>) */
.fi-grid-equal-rows > .fi-section {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    min-height: 100% !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}

.fi-grid-equal-rows > .fi-section > .fi-section-header {
    flex: 0 0 auto !important;
    height: auto !important;
    min-height: 0 !important;
    align-items: flex-start !important;
}

.fi-grid-equal-rows > .fi-section > .fi-section-content-ctn {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
}

.fi-grid-equal-rows > .fi-section > .fi-section-content-ctn > .fi-section-content {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    height: 100% !important;
}

/*
 * Pairing with .grid-container / .grid-item
 *   <div class="grid-container fi-grid-equal-rows">
 *     <div class="grid-item">
 *       <x-filament::section class="fi-stretch">…
 */
.grid-container.fi-grid-equal-rows > .grid-item > .fi-section {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    min-height: 100% !important;
    width: 100% !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}
</style>
