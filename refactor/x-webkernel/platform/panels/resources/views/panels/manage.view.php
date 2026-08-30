@once('wds.form-row')
<style>
.wds-form-row {
  display: grid;
  grid-template-columns: max(12em, 26%) 1fr;
  gap: 1.25em;
  padding: 1.15em 1.25em;
  border-bottom: 1px solid var(--wds-border);
  align-items: start;
  color: var(--wds-text);
}
.wds-form-row:last-child { border-bottom: 0; }
.wds-form-row > label { font-weight: 500; padding-top: 0.45em; }
.wds-form-row input[type="text"],
.wds-form-row input[type="url"],
.wds-form-row input[type="email"],
.wds-form-row input[type="number"],
.wds-form-row input[type="color"],
.wds-form-row select,
.wds-form-row textarea {
  max-width: 32em; width: 100%; font: inherit; color: var(--wds-text);
  background: var(--wds-surface); border: 1px solid var(--wds-border-strong);
  border-radius: var(--wds-radius); padding: 0.55em 0.8em;
}
.wds-form-row textarea { min-height: 6em; font-family: var(--wds-font-mono); font-size: 12px; }
.wds-form-hint { display: block; margin-top: 0.35em; font-size: 12px; color: var(--wds-text-muted); }
.wds-form-actions {
  position: sticky; bottom: 0; padding: 1em 1.25em;
  background: var(--wds-surface); border-top: 1px solid var(--wds-border);
}
.wds-flash {
  padding: 0.8em 1em; border-radius: var(--wds-radius);
  background: color-mix(in srgb, var(--primary-600) 12%, transparent);
  color: var(--wds-text);
}
@media (max-width: 720px) {
  .wds-form-row { grid-template-columns: 1fr; gap: 0.4em; }
  .wds-form-row > label { padding-top: 0; }
}
</style>
@endonce
@php
  $v = $values ?? [];
  $g = static fn (string $key, string $default = '') => (string) ($v[$key] ?? $default);
  $on = static fn (string $key): bool => (($v[$key] ?? '0') === '1' || ($v[$key] ?? false) === true);
  $logo_light = $g('logo_light', $g('logo'));
  $logo_alt = $g('logo_alt', $g('name'));
@endphp
@if (!empty($saved))
  <p class="wds-flash">{{ lang('panel.saved') }}</p>
@endif

    <form method="post" action="">
      {!! \Webkernel\Csrf::field() !!}
      <input type="hidden" name="edit_locale" value="{{ $edit_locale }}" />

      <x-webkernel::tabs :label="lang('panel.manage')">
        @slot('list')
          <x-webkernel::tabs.item tab="branding" :active="true" icon="palette">{{ lang('panel.tab.branding') }}</x-webkernel::tabs.item>
          <x-webkernel::tabs.item tab="locale" icon="languages">{{ lang('panel.tab.locale') }}</x-webkernel::tabs.item>
          <x-webkernel::tabs.item tab="legal" icon="scale">{{ lang('panel.tab.legal') }}</x-webkernel::tabs.item>
          <x-webkernel::tabs.item tab="system" icon="settings">{{ lang('panel.tab.system') }}</x-webkernel::tabs.item>
        @endslot

        <x-webkernel::tabs.panel tab="branding" :active="true">
          <x-webkernel::section :heading="lang('panel.tab.branding')">
            <div class="wds-form-row">
              <label for="name">{{ lang('panel.field.name') }}</label>
              <input id="name" type="text" name="name" value="{{ $g('name', (string) ($panel['label'] ?? '')) }}" />
            </div>
            <div class="wds-form-row">
              <label for="favicon">{{ lang('panel.field.favicon') }}</label>
              <input id="favicon" type="url" name="favicon" value="{{ $g('favicon') }}" placeholder="https://" />
            </div>
            <div class="wds-form-row">
              <label for="og_image">{{ lang('panel.field.og_image') }}</label>
              <input id="og_image" type="url" name="og_image" value="{{ $g('og_image') }}" placeholder="https://" />
            </div>
            <div class="wds-form-row">
              <label for="logo_light">{{ lang('panel.field.logo_light') }}</label>
              <div>
                <input id="logo_light" type="url" name="logo_light" value="{{ $logo_light }}" placeholder="https://" />
                @if ($logo_light !== '')
                  <div style="margin-top:0.6em"><x-webkernel::avatar :src="$logo_light" :alt="$logo_alt" size="lg" /></div>
                @endif
              </div>
            </div>
            <div class="wds-form-row">
              <label for="logo_dark">{{ lang('panel.field.logo_dark') }}</label>
              <input id="logo_dark" type="url" name="logo_dark" value="{{ $g('logo_dark') }}" placeholder="https://" />
            </div>
            <div class="wds-form-row">
              <label for="logo_height">{{ lang('panel.field.logo_size') }}</label>
              <div style="display:flex;gap:0.5em;max-width:20em">
                <input id="logo_height" type="text" name="logo_height" value="{{ $g('logo_height', '2rem') }}" placeholder="h" />
                <input type="text" name="logo_width" value="{{ $g('logo_width') }}" placeholder="w" />
              </div>
            </div>
            <div class="wds-form-row">
              <label for="logo_alt">{{ lang('panel.field.logo_alt') }}</label>
              <input id="logo_alt" type="text" name="logo_alt" value="{{ $g('logo_alt') }}" />
            </div>
            <div class="wds-form-row">
              <label for="logo_shape">{{ lang('panel.logo_shape') }}</label>
              <select id="logo_shape" name="logo_shape">
                @foreach (['favicon','round','square','responsive'] as $shape)
                  <option value="{{ $shape }}"{{ $g('logo_shape', 'favicon') === $shape ? ' selected' : '' }}>{{ lang('panel.logo_shape_'.$shape) }}</option>
                @endforeach
              </select>
            </div>
            <div class="wds-form-row">
              <label>{{ lang('panel.field.colors') }}</label>
              <div style="display:grid;grid-template-columns:repeat(3,minmax(0,8em));gap:0.75em">
                <label class="wds-form-hint">{{ lang('panel.field.primary') }}<input type="color" name="color_primary" value="{{ $g('color_primary', '#2563eb') }}" /></label>
                <label class="wds-form-hint">{{ lang('panel.field.secondary') }}<input type="color" name="color_secondary" value="{{ $g('color_secondary', '#0f172a') }}" /></label>
                <label class="wds-form-hint">{{ lang('panel.field.accent') }}<input type="color" name="color_accent" value="{{ $g('color_accent', '#0ea5e9') }}" /></label>
                <label class="wds-form-hint">{{ lang('panel.field.primary') }} (dark)<input type="color" name="color_primary_dark" value="{{ $g('color_primary_dark', '#60a5fa') }}" /></label>
                <label class="wds-form-hint">{{ lang('panel.field.secondary') }} (dark)<input type="color" name="color_secondary_dark" value="{{ $g('color_secondary_dark', '#e2e8f0') }}" /></label>
                <label class="wds-form-hint">{{ lang('panel.field.accent') }} (dark)<input type="color" name="color_accent_dark" value="{{ $g('color_accent_dark', '#38bdf8') }}" /></label>
              </div>
            </div>
          </x-webkernel::section>
        </x-webkernel::tabs.panel>

        <x-webkernel::tabs.panel tab="locale">
          <x-webkernel::section :heading="lang('panel.tab.locale')" :description="lang('panel.locale_help')">
            <div class="wds-form-row">
              <label for="locale">{{ lang('panel.field.locale') }}</label>
              <input id="locale" type="text" name="locale" value="{{ $g('locale', 'en') }}" />
            </div>
            <div class="wds-form-row">
              <label for="allowed_locales">{{ lang('panel.field.allowed_locales') }}</label>
              <div>
                <input id="allowed_locales" type="text" name="allowed_locales" value="{{ $g('allowed_locales', 'en,fr,ar,es,de,pt') }}" />
                <span class="wds-form-hint">{{ lang('panel.field.allowed_locales_hint') }}</span>
              </div>
            </div>
            <div class="wds-form-row">
              <label>{{ lang('panel.field.rtl') }}</label>
              <x-webkernel::toggle name="rtl" :checked="$on('rtl')" />
            </div>
            <div class="wds-form-row">
              <label for="site_title">{{ lang('panel.field.site_title') }} ({{ $edit_locale }})</label>
              <input id="site_title" type="text" name="site_title" value="{{ $i18n['site_title'] ?? '' }}" />
            </div>
            <div class="wds-form-row">
              <label for="meta_description">{{ lang('panel.field.meta_description') }} ({{ $edit_locale }})</label>
              <textarea id="meta_description" name="meta_description">{{ $i18n['meta_description'] ?? '' }}</textarea>
            </div>
            <div class="wds-form-row">
              <label for="keywords">{{ lang('panel.field.keywords') }} ({{ $edit_locale }})</label>
              <input id="keywords" type="text" name="keywords" value="{{ $i18n['keywords'] ?? '' }}" />
            </div>
          </x-webkernel::section>
        </x-webkernel::tabs.panel>

        <x-webkernel::tabs.panel tab="legal">
          <x-webkernel::section :heading="lang('panel.tab.legal')">
            <div class="wds-form-row">
              <label for="support_email">{{ lang('panel.field.support_email') }}</label>
              <input id="support_email" type="email" name="support_email" value="{{ $g('support_email') }}" />
            </div>
            <div class="wds-form-row">
              <label for="help_url">{{ lang('panel.field.help_url') }}</label>
              <input id="help_url" type="url" name="help_url" value="{{ $g('help_url') }}" />
            </div>
            <div class="wds-form-row">
              <label for="terms_url">{{ lang('panel.field.terms_url') }}</label>
              <input id="terms_url" type="url" name="terms_url" value="{{ $g('terms_url') }}" />
            </div>
            <div class="wds-form-row">
              <label for="privacy_url">{{ lang('panel.field.privacy_url') }}</label>
              <input id="privacy_url" type="url" name="privacy_url" value="{{ $g('privacy_url') }}" />
            </div>
            <div class="wds-form-row">
              <label for="copyright">{{ lang('panel.field.copyright') }}</label>
              <input id="copyright" type="text" name="copyright" value="{{ $g('copyright') }}" />
            </div>
            <div class="wds-form-row">
              <label>{{ lang('panel.field.social') }}</label>
              <div style="display:flex;flex-direction:column;gap:0.5em;max-width:32em">
                <input type="text" name="social_twitter" value="{{ $g('social_twitter') }}" placeholder="Twitter / X" />
                <input type="text" name="social_github" value="{{ $g('social_github') }}" placeholder="GitHub" />
                <input type="text" name="social_facebook" value="{{ $g('social_facebook') }}" placeholder="Facebook" />
              </div>
            </div>
          </x-webkernel::section>
        </x-webkernel::tabs.panel>

        <x-webkernel::tabs.panel tab="system">
          <x-webkernel::section :heading="lang('panel.tab.system')">
            <div class="wds-form-row">
              <label for="timezone">{{ lang('panel.field.timezone') }}</label>
              <input id="timezone" type="text" name="timezone" value="{{ $g('timezone', 'UTC') }}" />
            </div>
            <div class="wds-form-row">
              <label>{{ lang('panel.field.datetime') }}</label>
              <div style="display:flex;gap:0.5em;max-width:24em">
                <input type="text" name="date_format" value="{{ $g('date_format', 'Y-m-d') }}" />
                <input type="text" name="time_format" value="{{ $g('time_format', 'H:i') }}" />
              </div>
            </div>
            <div class="wds-form-row">
              <label for="currency">{{ lang('panel.field.currency') }}</label>
              <input id="currency" type="text" name="currency" value="{{ $g('currency', 'USD') }}" />
            </div>
            <div class="wds-form-row">
              <label>{{ lang('panel.field.maintenance') }}</label>
              <x-webkernel::toggle name="maintenance" :checked="$on('maintenance')" />
            </div>
            <div class="wds-form-row">
              <label for="maintenance_message">{{ lang('panel.field.maintenance_message') }}</label>
              <textarea id="maintenance_message" name="maintenance_message">{{ $g('maintenance_message') }}</textarea>
            </div>
            <div class="wds-form-row">
              <label for="analytics_id">{{ lang('panel.field.analytics_id') }}</label>
              <input id="analytics_id" type="text" name="analytics_id" value="{{ $g('analytics_id') }}" />
            </div>
            <div class="wds-form-row">
              <label for="header_css">{{ lang('panel.field.header_css') }}</label>
              <textarea id="header_css" name="header_css">{{ $g('header_css') }}</textarea>
            </div>
            <div class="wds-form-row">
              <label for="footer_js">{{ lang('panel.field.footer_js') }}</label>
              <textarea id="footer_js" name="footer_js">{{ $g('footer_js') }}</textarea>
            </div>
            <div class="wds-form-row">
              <label for="session_timeout">{{ lang('panel.field.session_timeout') }}</label>
              <input id="session_timeout" type="number" name="session_timeout" value="{{ $g('session_timeout', '120') }}" min="1" />
            </div>
          </x-webkernel::section>
        </x-webkernel::tabs.panel>
      </x-webkernel::tabs>

      <div class="wds-form-actions">
        <x-webkernel::button type="submit" color="primary">{{ lang('panel.save') }}</x-webkernel::button>
      </div>
    </form>
