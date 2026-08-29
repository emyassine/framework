<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\System\Pages;

use Webkernel\View\View;

final class Dashboard
{
    /**
     * @param $path string
     *
     * @return array{class: class-string, path: string, methods: list<string>}
     */
    public static function route(string $path = '/'): array
    {
        return ['class' => self::class, 'path' => $path, 'methods' => ['GET']];
    }

    /**
     * @return string
     */
    public function __invoke(): string
    {
        return View::make('webkernel::system.dashboard')->render();
    }
}
