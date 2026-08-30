@php
  $current = \function_exists('i18n_current_lang') ? i18n_current_lang() : 'en';
  $codes = ['en', 'fr', 'ar', 'es', 'de', 'pt'];
  $back = \parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
@endphp
@once('wds.lang')
<style>
.wds-lang { position: relative; }
.wds-lang-trigger {
  display: inline-flex; align-items: center; gap: 0.5em;
  padding: 0.4em 0.65em; border-radius: 6px; min-height: 40px;
  color: var(--wds-text);
}
.wds-lang-trigger:hover { background: var(--wds-bg-subtle); }
.wds-lang-flag { width: 1.2em; height: 1.2em; display: inline-flex; overflow: hidden; border-radius: 2px; }
.wds-lang-flag svg { width: 100%; height: 100%; }
.wds-lang-panel {
  display: none; position: absolute; inset-inline-end: 0; top: calc(100% + 0.4rem);
  width: 18rem; max-height: min(15rem, 50vh); overflow: auto;
  background: var(--wds-surface); border: 1px solid var(--wds-border);
  border-radius: 8px; z-index: 70; padding: 4px;
}
.wds-lang.wds-open .wds-lang-panel { display: block; }
.wds-lang-panel button {
  display: flex; align-items: center; gap: 0.6em; padding: 0.55em 0.75em;
  border-radius: 6px; font-size: 13px; font-weight: 500; width: 100%;
  color: inherit; text-align: start;
}
.wds-lang-panel button:hover { background: var(--wds-bg-subtle); }
.wds-lang-panel button.wds-active { background: color-mix(in srgb, var(--primary-600) 12%, transparent); }
@media (max-width: 640px) {
  .wds-lang-trigger span:not(.wds-lang-flag) { display: none; }
  .wds-lang-panel { width: min(18rem, calc(100vw - 2rem)); }
}
</style>
@endonce
@once('wds.lang.js')
<script>
(function () {
  document.addEventListener('click', function (event) {
    document.querySelectorAll('[data-wds-lang]').forEach(function (box) {
      if (!box.contains(event.target)) box.classList.remove('wds-open');
    });
  });
})();
</script>
@endonce
<div class="wds-lang" data-wds-lang>
  <button type="button" class="wds-lang-trigger" onclick="this.parentElement.classList.toggle('wds-open')" title="{{ lang('panel.language') }}">
    <span class="wds-lang-flag">{!! flag_markup($current) !!}</span>
    <span>{{ \function_exists('i18n_catalog_language_label') ? i18n_catalog_language_label($current) : $current }}</span>
  </button>
  <form class="wds-lang-panel" method="post" action="/locale" role="menu">
    {!! \Webkernel\Csrf::field() !!}
    <input type="hidden" name="_back" value="{{ $back }}" />
    @foreach ($codes as $code)
      <button type="submit" name="locale" value="{{ $code }}" class="{{ $code === $current ? 'wds-active' : '' }}" role="menuitem">
        <span class="wds-lang-flag">{!! flag_markup($code) !!}</span>
        <span>{{ \function_exists('i18n_catalog_language_label') ? i18n_catalog_language_label($code) : $code }}</span>
      </button>
    @endforeach
  </form>
</div>
