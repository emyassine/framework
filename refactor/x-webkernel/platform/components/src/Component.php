<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Component\Component as BaseComponent;
use Webkernel\Platform\Components\Concerns\HasLayout;
use Webkernel\Platform\Components\Concerns\HasMethodMake;

/**
 * Dual-use UI atom: View plus a dumpable PHP declaration.
 *
 * //> `<x-webkernel::{name}>` and `::make()` render the same `.view.php`.
 * //> This extends the base Component with platform-specific features (layout, make).
 *
 * @mixin HasMethodMake
 * @mixin HasLayout
 *
 * @method static static make(string $name = '')
 */
abstract class Component extends BaseComponent
{
    use HasLayout;
    use HasMethodMake;

    /**
     * @param string $label
     *
     * @return static
     */
    public function label(string $label): static
    {
        $this->props['label'] = $label;

        return $this;
    }

    /**
     * Set a slot content.
     *
     * @param string $html
     * @return static
     */
    public function slot(string $html): static
    {
        $this->props['slot'] = $html;

        return $this;
    }

    /**
     * Get the component declaration for dumping.
     *
     * @return array{component: class-string, view: string, props: array<string, mixed>}
     */
    public function to_array(): array
    {
        return [
            'component' => static::class,
            'view' => $this->view(),
            'props' => $this->to_props(),
        ];
    }
}
