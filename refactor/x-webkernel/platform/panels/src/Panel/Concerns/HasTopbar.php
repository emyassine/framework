<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Panel\Concerns;

/**
 * Page top bar: breadcrumbs, locale, theme, user menu.
 *
 * @method self topbar(bool $enabled = true)
 * @method bool has_topbar()
 */
trait HasTopbar
{
    private bool $has_topbar = true;

    /**
     * Show or hide the top bar.
     *
     * @param $enabled bool
     * @return self
     */
    public function topbar(bool $enabled = true): self
    {
        $this->has_topbar = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function has_topbar(): bool
    {
        return $this->has_topbar;
    }

    /**
     * @return array{topbar: bool}
     */
    private function topbar_chrome(): array
    {
        return [
            'topbar' => $this->has_topbar,
        ];
    }
}
