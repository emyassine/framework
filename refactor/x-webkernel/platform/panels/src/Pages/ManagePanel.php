<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Pages;

use Webkernel\Config\Config;
use Webkernel\Csrf;
use Webkernel\View\View;

/**
 * Injected into every panel. App owner sets that panel's mark (url + shape).
 */
final class ManagePanel
{
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
        $saved = false;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $id !== '' && Csrf::check()) {
            $logo = \trim((string) ($_POST['logo'] ?? ''));
            $shape = (string) ($_POST['logo_shape'] ?? 'favicon');
            if ($shape !== 'round' && $shape !== 'square' && $shape !== 'favicon') {
                $shape = 'favicon';
            }
            Config::set('panels.'.$id.'.logo', $logo);
            Config::set('panels.'.$id.'.logo_shape', $shape);
            $saved = true;
            $panel = \webapp()->panel()->matching_path();
        }

        return View::make('webkernel::panels.manage', [
            'panel' => \is_array($panel) ? $panel : [],
            'logo' => $id !== '' ? (string) Config::get('panels.'.$id.'.logo', '') : '',
            'logo_shape' => $id !== '' ? (string) Config::get('panels.'.$id.'.logo_shape', 'favicon') : 'favicon',
            'saved' => $saved,
        ])->render();
    }
}
