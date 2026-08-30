<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Pages;

use Webkernel\Config\Config;
use Webkernel\Csrf;
use Webkernel\I18n\I18nContext;
use Webkernel\Platform\Components\Checkbox;
use Webkernel\Platform\Components\Select;
use Webkernel\Platform\Components\Tabs;
use Webkernel\Platform\Components\TextInput;
use Webkernel\Platform\Notification;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\View\Liveview;

/**
 * Injected into every panel. App owner edits branding, locale, legal, and system keys.
 *
 * //> Fields live on this class. The view renders Schema + a save button.
 */
final class ManagePanel extends Page
{
    protected static string $slug = 'manage';

    /** @var list<string> */
    private const STRINGS = [
        'name',
        'favicon',
        'og_image',
        'logo_light',
        'logo_dark',
        'logo_height',
        'logo_width',
        'logo_alt',
        'logo_shape',
        'color_primary',
        'color_secondary',
        'color_accent',
        'color_primary_dark',
        'color_secondary_dark',
        'color_accent_dark',
        'locale',
        'allowed_locales',
        'support_email',
        'help_url',
        'terms_url',
        'privacy_url',
        'copyright',
        'social_twitter',
        'social_github',
        'social_facebook',
        'timezone',
        'date_format',
        'time_format',
        'currency',
        'maintenance_message',
        'analytics_id',
        'header_css',
        'footer_js',
        'session_timeout',
    ];

    /** @var list<string> */
    private const BOOLS = ['rtl', 'maintenance'];

    /** @var list<string> */
    private const I18N = ['site_title', 'meta_description', 'keywords'];

    /** @var list<string> */
    private const SHAPES = ['favicon', 'round', 'square', 'responsive'];

    /** @var array<string, string> */
    private array $errors = [];

    /**
     * @param $path string
     * @param $methods list<string>
     *
     * @return array{class: class-string, path: string, methods: list<string>}
     */
    public static function route(string $path = '/manage', array $methods = ['GET', 'POST']): array
    {
        return parent::route($path, $methods);
    }

    /**
     * @return string
     */
    public function get_header(): string
    {
        return $this->lang('panel.manage', 'Manage');
    }

    /**
     * @return string
     */
    public function get_subheader(): string
    {
        return $this->lang('panel.manage_help', '');
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::panels.manage';
    }

    /**
     * @param $state array<string, mixed>
     *
     * @return Schema
     */
    public function schema(array $state): Schema
    {
        return Schema::make()->components([
            Tabs::make()->contained()->tabs([
                [
                    'id' => 'branding',
                    'label' => $this->lang('panel.tab.branding', 'Branding'),
                    'icon' => 'palette',
                    'schema' => $this->branding_schema(),
                ],
                [
                    'id' => 'locale',
                    'label' => $this->lang('panel.tab.locale', 'Locale'),
                    'icon' => 'languages',
                    'schema' => $this->locale_schema(),
                ],
                [
                    'id' => 'legal',
                    'label' => $this->lang('panel.tab.legal', 'Legal'),
                    'icon' => 'scale',
                    'schema' => $this->legal_schema(),
                ],
                [
                    'id' => 'system',
                    'label' => $this->lang('panel.tab.system', 'System'),
                    'icon' => 'settings',
                    'schema' => $this->system_schema(),
                ],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function view_data(): array
    {
        $panel = \webapp()->panel()->matching_path();
        $id = \is_array($panel) ? (string) ($panel['id'] ?? '') : '';
        $edit_locale = I18nContext::normalize((string) ($_POST['edit_locale'] ?? I18nContext::get_locale()));
        if ($edit_locale === '') {
            $edit_locale = 'en';
        }
        $values = $id !== '' ? $this->values($id, $edit_locale) : [];
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $id !== '' && Csrf::check()) {
            $posted = $this->posted();
            $this->errors = $this->validate($posted);
            if ($this->errors === []) {
                $this->save($id, $edit_locale, $posted);
                Notification::make()
                    ->title($this->lang('panel.saved', 'Saved'))
                    ->success()
                    ->send();
                $values = $this->values($id, $edit_locale);
            } elseif (Liveview::is_request()) {
                \http_response_code(422);
            }
        }
        $action = (string) (\parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/manage'), \PHP_URL_PATH) ?: '/manage');

        return [
            'panel' => \is_array($panel) ? $panel : [],
            'id' => $id,
            'values' => $values,
            'edit_locale' => $edit_locale,
            'errors' => $this->errors,
            'schema' => $this->schema($values)->render($values, $this->errors),
            'action' => $action,
        ];
    }

    /**
     * @param $id string
     * @param $edit_locale string
     * @param $data array<string, string>
     *
     * @return void
     */
    private function save(string $id, string $edit_locale, array $data): void
    {
        $prefix = 'panels.'.$id.'.';
        foreach (self::STRINGS as $key) {
            if (! \array_key_exists($key, $data)) {
                continue;
            }
            Config::set($prefix.$key, $data[$key]);
        }
        foreach (self::BOOLS as $key) {
            Config::set($prefix.$key, ($data[$key] ?? '0') === '1' ? '1' : '0');
        }
        foreach (self::I18N as $key) {
            if (! \array_key_exists($key, $data)) {
                continue;
            }
            Config::set($prefix.'i18n.'.$edit_locale.'.'.$key, $data[$key]);
        }
        if (\array_key_exists('logo_light', $data)) {
            Config::set($prefix.'logo', $data['logo_light']);
        }
    }

    /**
     * @param $data array<string, string>
     *
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];
        $shape = $data['logo_shape'] ?? 'favicon';
        if (! \in_array($shape, self::SHAPES, true)) {
            $errors['logo_shape'] = $this->lang('panel.error.logo_shape', 'Unknown logo shape.');
        }
        foreach (['color_primary', 'color_secondary', 'color_accent', 'color_primary_dark', 'color_secondary_dark', 'color_accent_dark'] as $key) {
            $hex = \trim($data[$key] ?? '');
            if ($hex !== '' && \Webkernel\Platform\Colors\Color::normalize_hex($hex) === '') {
                $errors[$key] = $this->lang('panel.error.color', 'Use a hex color.');
            }
        }
        $email = \trim($data['support_email'] ?? '');
        if ($email !== '' && \filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            $errors['support_email'] = $this->lang('panel.error.email', 'Invalid email.');
        }
        $timeout = \trim($data['session_timeout'] ?? '');
        if ($timeout !== '' && ! \ctype_digit($timeout)) {
            $errors['session_timeout'] = $this->lang('panel.error.timeout', 'Timeout must be a number.');
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    private function posted(): array
    {
        $out = [];
        foreach ([...self::STRINGS, ...self::I18N] as $key) {
            if (\array_key_exists($key, $_POST)) {
                $out[$key] = \trim((string) $_POST[$key]);
            }
        }
        foreach (self::BOOLS as $key) {
            $out[$key] = isset($_POST[$key]) && (string) $_POST[$key] === '1' ? '1' : '0';
        }
        if (($out['logo_shape'] ?? '') === '' || ! \in_array($out['logo_shape'] ?? '', self::SHAPES, true)) {
            $out['logo_shape'] = 'favicon';
        }

        return $out;
    }

    /**
     * @param $id string
     * @param $locale string
     *
     * @return array<string, mixed>
     */
    private function values(string $id, string $locale): array
    {
        $raw = Config::get('panels.'.$id, []);
        $out = \is_array($raw) ? $raw : [];
        $i18n = Config::get('panels.'.$id.'.i18n.'.$locale, []);
        if (\is_array($i18n)) {
            foreach (self::I18N as $key) {
                if (isset($i18n[$key]) && (\is_string($i18n[$key]) || \is_numeric($i18n[$key]))) {
                    $out[$key] = (string) $i18n[$key];
                }
            }
        }
        if (! isset($out['logo_light']) && isset($out['logo'])) {
            $out['logo_light'] = $out['logo'];
        }
        foreach (self::BOOLS as $key) {
            $out[$key] = (($out[$key] ?? '0') === '1' || ($out[$key] ?? false) === true);
        }

        return $out;
    }

    /**
     * @return Schema
     */
    private function branding_schema(): Schema
    {
        return Schema::make()->components([
            TextInput::make('name')->label($this->lang('panel.field.name', 'Name')),
            TextInput::make('site_title')->label($this->lang('panel.field.site_title', 'Site title')),
            TextInput::make('meta_description')->label($this->lang('panel.field.meta_description', 'Meta description')),
            TextInput::make('keywords')->label($this->lang('panel.field.keywords', 'Keywords')),
            TextInput::make('favicon')->label($this->lang('panel.field.favicon', 'Favicon'))->type('url'),
            TextInput::make('og_image')->label($this->lang('panel.field.og_image', 'Open Graph image'))->type('url'),
            TextInput::make('logo_light')->label($this->lang('panel.field.logo_light', 'Logo (light)'))->type('url'),
            TextInput::make('logo_dark')->label($this->lang('panel.field.logo_dark', 'Logo (dark)'))->type('url'),
            TextInput::make('logo_height')->label($this->lang('panel.field.logo_height', 'Logo height')),
            TextInput::make('logo_width')->label($this->lang('panel.field.logo_width', 'Logo width')),
            TextInput::make('logo_alt')->label($this->lang('panel.field.logo_alt', 'Logo alt')),
            Select::make('logo_shape')->label($this->lang('panel.field.logo_shape', 'Logo shape'))->options([
                'favicon' => 'Favicon',
                'round' => 'Round',
                'square' => 'Square',
                'responsive' => 'Responsive',
            ]),
            TextInput::make('color_primary')->label($this->lang('panel.field.primary', 'Primary'))->type('color'),
            TextInput::make('color_secondary')->label($this->lang('panel.field.secondary', 'Secondary'))->type('color'),
            TextInput::make('color_accent')->label($this->lang('panel.field.accent', 'Accent'))->type('color'),
            TextInput::make('color_primary_dark')->label($this->lang('panel.field.primary', 'Primary').' (dark)')->type('color'),
            TextInput::make('color_secondary_dark')->label($this->lang('panel.field.secondary', 'Secondary').' (dark)')->type('color'),
            TextInput::make('color_accent_dark')->label($this->lang('panel.field.accent', 'Accent').' (dark)')->type('color'),
        ]);
    }

    /**
     * @return Schema
     */
    private function locale_schema(): Schema
    {
        return Schema::make()->components([
            TextInput::make('locale')->label($this->lang('panel.field.locale', 'Locale')),
            TextInput::make('allowed_locales')->label($this->lang('panel.field.allowed_locales', 'Allowed locales'))
                ->hint($this->lang('panel.field.allowed_locales_hint', '')),
            Checkbox::make('rtl')->label($this->lang('panel.field.rtl', 'RTL')),
            TextInput::make('timezone')->label($this->lang('panel.field.timezone', 'Timezone')),
            TextInput::make('date_format')->label($this->lang('panel.field.date_format', 'Date format')),
            TextInput::make('time_format')->label($this->lang('panel.field.time_format', 'Time format')),
            TextInput::make('currency')->label($this->lang('panel.field.currency', 'Currency')),
        ]);
    }

    /**
     * @return Schema
     */
    private function legal_schema(): Schema
    {
        return Schema::make()->components([
            TextInput::make('support_email')->label($this->lang('panel.field.support_email', 'Support email'))->type('email'),
            TextInput::make('help_url')->label($this->lang('panel.field.help_url', 'Help URL'))->type('url'),
            TextInput::make('terms_url')->label($this->lang('panel.field.terms_url', 'Terms URL'))->type('url'),
            TextInput::make('privacy_url')->label($this->lang('panel.field.privacy_url', 'Privacy URL'))->type('url'),
            TextInput::make('copyright')->label($this->lang('panel.field.copyright', 'Copyright')),
            TextInput::make('social_twitter')->label($this->lang('panel.field.social_twitter', 'Twitter')),
            TextInput::make('social_github')->label($this->lang('panel.field.social_github', 'GitHub')),
            TextInput::make('social_facebook')->label($this->lang('panel.field.social_facebook', 'Facebook')),
        ]);
    }

    /**
     * @return Schema
     */
    private function system_schema(): Schema
    {
        return Schema::make()->components([
            Checkbox::make('maintenance')->label($this->lang('panel.field.maintenance', 'Maintenance')),
            TextInput::make('maintenance_message')->label($this->lang('panel.field.maintenance_message', 'Maintenance message'))->type('textarea'),
            TextInput::make('analytics_id')->label($this->lang('panel.field.analytics_id', 'Analytics ID')),
            TextInput::make('header_css')->label($this->lang('panel.field.header_css', 'Header CSS'))->type('textarea'),
            TextInput::make('footer_js')->label($this->lang('panel.field.footer_js', 'Footer JS'))->type('textarea'),
            TextInput::make('session_timeout')->label($this->lang('panel.field.session_timeout', 'Session timeout'))->type('number'),
        ]);
    }

    /**
     * @param $key string
     * @param $fallback string
     *
     * @return string
     */
    private function lang(string $key, string $fallback): string
    {
        if (\function_exists('lang')) {
            $value = lang($key);
            if (\is_string($value) && $value !== '' && $value !== $key) {
                return $value;
            }
        }

        return $fallback;
    }
}
