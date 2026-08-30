<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Flexbox row that stacks below a breakpoint. View: `<x-webkernel::flex>`.
 */
final class Flex extends LayoutComponent
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::flex';
    }

    /**
     * @param $breakpoint string
     *
     * @return static
     */
    public function from(string $breakpoint): static
    {
        $this->props['from'] = $breakpoint;

        return $this;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        $extra['from'] = (string) ($this->props['from'] ?? 'md');

        return parent::render($extra);
    }
}
