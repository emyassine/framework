{{--
  utility-classes/_grid
  ─────────────────────
  Generic CSS grid helpers for card layouts.

  Usage
  ─────
    <div class="grid-container grid-cols-4">
      <div class="grid-item">…</div>
    </div>

  Equal-height Filament cards (pair with _fi-stretch):
    <div class="grid-container grid-cols-4 fi-grid-equal-rows">
      <div class="grid-item">
        <x-filament::section class="fi-stretch">…</x-filament::section>
      </div>
    </div>

  Or any list grid:
    <ul class="md-dash__grid fi-grid-equal-rows">
      <li><x-filament::section class="fi-stretch">…</x-filament::section></li>
    </ul>
--}}
<style id="webkernel-utility-grid">
.grid-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
    margin-top: 1rem;
    align-items: stretch;
}

@media (min-width: 640px) {
    .grid-container {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Desktop column count — default 4; override with .grid-cols-2|3|4… */
@media (min-width: 1024px) {
    .grid-container {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .grid-container.grid-cols-2  { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-container.grid-cols-3  { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .grid-container.grid-cols-4  { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .grid-container.grid-cols-5  { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .grid-container.grid-cols-6  { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .grid-container.grid-cols-7  { grid-template-columns: repeat(7, minmax(0, 1fr)); }
    .grid-container.grid-cols-8  { grid-template-columns: repeat(8, minmax(0, 1fr)); }
    .grid-container.grid-cols-9  { grid-template-columns: repeat(9, minmax(0, 1fr)); }
    .grid-container.grid-cols-10 { grid-template-columns: repeat(10, minmax(0, 1fr)); }
    .grid-container.grid-cols-11 { grid-template-columns: repeat(11, minmax(0, 1fr)); }
    .grid-container.grid-cols-12 { grid-template-columns: repeat(12, minmax(0, 1fr)); }
}

/*
 * Cell wrapper: flex column so a direct child (.fi-section, card, …)
 * can flex:1 / height:100% and fill the stretched grid row.
 */
.grid-item {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-width: 0;
    min-height: 0;
    align-self: stretch;
    text-decoration: none !important;
}

.grid-item > * {
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    min-height: 0;
    box-sizing: border-box;
}

/* When child is a Filament section, keep it a column flex shell */
.grid-item > .fi-section {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 100%;
}
</style>
