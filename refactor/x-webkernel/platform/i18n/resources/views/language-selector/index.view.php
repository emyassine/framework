@php
  $current = \function_exists('i18n_current_lang') ? i18n_current_lang() : 'en';
  $codes = ['en', 'fr', 'ar', 'es', 'de', 'pt'];
  $path = \parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $query = $_GET;
@endphp
<div class="wds-lang" data-wds-lang>
  <button type="button" class="wds-lang-trigger" onclick="this.parentElement.classList.toggle('wds-open')" title="{{ lang('panel.language') }}">
    <span class="wds-lang-flag">{!! flag_markup($current) !!}</span>
    <span>{{ \function_exists('i18n_catalog_language_label') ? i18n_catalog_language_label($current) : $current }}</span>
  </button>
  <div class="wds-lang-panel" role="menu">
    @foreach ($codes as $code)
      @php
        $query['lang'] = $code;
        $href = $path.'?'.\http_build_query($query);
      @endphp
      <a href="{{ $href }}" class="{{ $code === $current ? 'wds-active' : '' }}" role="menuitem">
        <span class="wds-lang-flag">{!! flag_markup($code) !!}</span>
        <span>{{ \function_exists('i18n_catalog_language_label') ? i18n_catalog_language_label($code) : $code }}</span>
      </a>
    @endforeach
  </div>
</div>
