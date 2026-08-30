<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Platform\Components\Concerns\HasMethodMake;
use Webkernel\View\View;

/**
 * Dual-use UI atom: View plus a dumpable PHP declaration.
 *
 * //> `<x-webkernel::{name}>` and `::make()` render the same `.view.php`.
 *
 * @mixin HasMethodMake
 *
 * @method static static make(string $name = '')
 */
abstract class Component
{
    use HasMethodMake;

    protected string $name = '';

    /** @var array<string, mixed> */
    protected array $props = [];

    /**
     * @param $label string
     *
     * @return static
     */
    public function label(string $label): static
    {
        $this->props['label'] = $label;

        return $this;
    }

    /**
     * @return string
     */
    abstract public function view(): string;

    /**
     * @return array<string, mixed>
     */
    public function to_props(): array
    {
        return \array_merge(['name' => $this->name], $this->props);
    }

    /**
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

    /**
     * @param $html string
     *
     * @return static
     */
    public function slot(string $html): static
    {
        $this->props['slot'] = $html;

        return $this;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        $data = \array_merge($this->to_props(), $extra);
        if (! isset($data['attributes']) || ! $data['attributes'] instanceof \Webkernel\View\AttributeBag) {
            $data['attributes'] = new \Webkernel\View\AttributeBag($data);
        }

        return View::make($this->view(), $data)->render();
    }
}
