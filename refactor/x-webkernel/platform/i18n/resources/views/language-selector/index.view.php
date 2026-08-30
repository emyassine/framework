@php
  $current = \function_exists('i18n_current_lang') ? i18n_current_lang() : 'en';
  $codes = ['en', 'fr', 'ar', 'es', 'de', 'pt'];
  $back = \parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
@endphp
<div class="w-lang" data-w-lang>
  <button type="button" class="w-lang-trigger" onclick="this.parentElement.classList.toggle('w-open')" title="{{ lang('panel.language') }}">
    <span class="w-lang-flag">{!! flag_markup($current) !!}</span>
    <span>{{ \function_exists('i18n_catalog_language_label') ? i18n_catalog_language_label($current) : $current }}</span>
  </button>
  <form class="w-lang-panel" method="post" action="/locale" role="menu">
    {!! \Webkernel\Csrf::field() !!}
    <input type="hidden" name="_back" value="{{ $back }}" />
    @foreach ($codes as $code)
      <button type="submit" name="locale" value="{{ $code }}" class="{{ $code === $current ? 'w-active' : '' }}" role="menuitem">
        <span class="w-lang-flag">{!! flag_markup($code) !!}</span>
        <span>{{ \function_exists('i18n_catalog_language_label') ? i18n_catalog_language_label($code) : $code }}</span>
      </button>
    @endforeach
  </form>
</div>
