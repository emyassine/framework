{{--
  Topbar: icon trigger. user-menu / sidebar: full-width trigger.
  Panel is <x-webkernel::dropdown>. Locale switch stays POST /locale.
--}}
@props([
  'context' => 'topbar',
  'languages' => null,
  'show_flags' => true,
])
@php
  $current = \function_exists('i18n_current_lang') ? i18n_current_lang() : 'en';
  $codes = \is_array($languages) && $languages !== [] ? $languages : ['en', 'fr', 'ar', 'es', 'de', 'pt'];
  $back = \parse_url($_SERVER['REQUEST_URI'] ?? '/', \PHP_URL_PATH) ?: '/';
  $context = \in_array($context, ['topbar', 'user-menu', 'sidebar'], true) ? $context : 'topbar';
  $is_topbar = $context === 'topbar';
  $rtl = \function_exists('i18n_is_rtl') && i18n_is_rtl();
  $placement = $is_topbar ? ($rtl ? 'bottom-end' : 'bottom-start') : 'top-end';
  $available = \function_exists('fast_i18n')
    ? fast_i18n([
      'en' => 'Available Languages',
      'fr' => 'Langues disponibles',
      'es' => 'Idiomas disponibles',
      'ar' => 'اللغات المتاحة',
    ], null, 'i18n_language_selector_available_languages')
    : 'Available Languages';
  $empty = \function_exists('fast_i18n')
    ? fast_i18n([
      'en' => 'No Available Languages',
      'fr' => 'Aucune langue disponible',
      'es' => 'No hay idiomas disponibles',
      'ar' => 'لا توجد لغات متاحة',
    ], null, 'i18n_language_selector_no_available_languages')
    : 'No Available Languages';
  $tooltip = \function_exists('fast_i18n')
    ? fast_i18n([
      'en' => 'Switch Language',
      'fr' => 'Changer de langue',
      'es' => 'Cambiar idioma',
      'ar' => 'تغيير اللغة',
    ], null, 'i18n_language_selector_switch_language')
    : 'Switch Language';
  $current_label = \function_exists('i18n_catalog_language_label')
    ? i18n_catalog_language_label($current)
    : $current;
@endphp
<div class="w-lang w-lang-{{ $context }}">
  <x-webkernel::dropdown :placement="$placement" width="lang">
    <x-slot name="trigger">
      <button type="button" class="w-lang-trigger" title="{{ $tooltip }}">
        @if ($show_flags)
          <span class="w-lang-flag">{!! flag_markup($current) !!}</span>
        @endif
        @if (! $is_topbar)
          <span class="w-lang-trigger-label">{{ $current_label }}</span>
        @endif
      </button>
    </x-slot>
    <x-webkernel::dropdown.header icon="languages">{{ $available }}</x-webkernel::dropdown.header>
    <form method="post" action="/locale" role="menu">
      {!! \Webkernel\Csrf::field() !!}
      <input type="hidden" name="_back" value="{{ $back }}" />
      <x-webkernel::dropdown.list class="w-lang-list">
        @forelse ($codes as $code)
          @php
            $label = \function_exists('i18n_catalog_language_label') ? i18n_catalog_language_label((string) $code) : (string) $code;
            $on = (string) $code === $current;
          @endphp
          <x-webkernel::dropdown.list.item
            type="submit"
            name="locale"
            :value="$code"
            :selected="$on"
            :icon="$on ? 'check' : null"
          >
            @if ($show_flags)
              <span class="w-lang-flag">{!! flag_markup((string) $code) !!}</span>
            @endif
            <span>{{ $label }}</span>
          </x-webkernel::dropdown.list.item>
        @empty
          <div class="w-lang-empty">{{ $empty }}</div>
        @endforelse
      </x-webkernel::dropdown.list>
    </form>
  </x-webkernel::dropdown>
</div>
