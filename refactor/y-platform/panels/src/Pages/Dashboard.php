<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Pages;

/**
 * Default panel landing page. System panel keeps its own Dashboard.
 */
class Dashboard extends Page
{
    public const HEADER = 'Overview';

    protected static string $slug = '/';

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::pages.dashboard';
    }
}
