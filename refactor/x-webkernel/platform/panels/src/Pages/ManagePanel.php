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
use Webkernel\View\View;

/**
 * Injected into every panel. App owner edits branding, locale, legal, and system keys.
 */
final class ManagePanel
{
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

    /**
     * @param $path string
     * @return array{class: class-string, path: string, methods: list<string>}
     */
    public static function route(string $path = '/manage'): array
    {
        return ['class' => self::class, 'path' => $path, 'methods' => ['GET', 'POST']];
    }

    /**
     * @return string
     */
    public function __invoke(): string
    {
        $panel = \webapp()->panel()->matching_path();
        $id = \is_array($panel) ? (string) ($panel['id'] ?? '') : '';
        $edit_locale = I18nContext::normalize((string) ($_POST['edit_locale'] ?? I18nContext::get_locale()));
        if ($edit_locale === '') {
            $edit_locale = 'en';
        }
        $saved = false;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $id !== '' && Csrf::check()) {
            $this->save($id, $edit_locale);
            $saved = true;
        }

        return View::make('webkernel::panels.manage', [
            'panel' => \is_array($panel) ? $panel : [],
            'id' => $id,
            'values' => $id !== '' ? $this->values($id) : [],
            'edit_locale' => $edit_locale,
            'i18n' => $id !== '' ? $this->i18n($id, $edit_locale) : [],
            'saved' => $saved,
        ])->render();
    }

    /**
     * @param $id string
     * @param $edit_locale string
     *
     * @return void
     */
    private function save(string $id, string $edit_locale): void
    {
        $prefix = 'panels.'.$id.'.';
        foreach (self::STRINGS as $key) {
            if (! \array_key_exists($key, $_POST)) {
                continue;
            }
            $value = \trim((string) $_POST[$key]);
            if ($key === 'logo_shape' && ! \in_array($value, ['favicon', 'round', 'square', 'responsive'], true)) {
                $value = 'favicon';
            }
            Config::set($prefix.$key, $value);
        }
        foreach (self::BOOLS as $key) {
            Config::set($prefix.$key, isset($_POST[$key]) && (string) $_POST[$key] === '1' ? '1' : '0');
        }
        foreach (self::I18N as $key) {
            if (! \array_key_exists($key, $_POST)) {
                continue;
            }
            Config::set($prefix.'i18n.'.$edit_locale.'.'.$key, \trim((string) $_POST[$key]));
        }
        $logo = \trim((string) ($_POST['logo_light'] ?? $_POST['logo'] ?? ''));
        if ($logo !== '' || \array_key_exists('logo_light', $_POST)) {
            Config::set($prefix.'logo', $logo);
        }
    }

    /**
     * @param $id string
     * @return array<string, mixed>
     */
    private function values(string $id): array
    {
        $raw = Config::get('panels.'.$id, []);

        return \is_array($raw) ? $raw : [];
    }

    /**
     * @param $id string
     * @param $locale string
     * @return array<string, string>
     */
    private function i18n(string $id, string $locale): array
    {
        $tree = Config::get('panels.'.$id.'.i18n.'.$locale, []);
        if (! \is_array($tree)) {
            return [];
        }
        $out = [];
        foreach ($tree as $key => $value) {
            if (\is_string($key) && (\is_string($value) || \is_numeric($value))) {
                $out[$key] = (string) $value;
            }
        }

        return $out;
    }
}
