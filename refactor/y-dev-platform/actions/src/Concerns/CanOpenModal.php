<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Actions\Concerns;

use Closure;

/**
 * Modal confirmation / form dialog props on an Action.
 *
 * @property array<string, mixed> $props
 */
trait CanOpenModal
{
    /**
     * @param $heading string|Closure|null
     *
     * @return static
     */
    public function modal_heading(string|Closure|null $heading): static
    {
        $this->props['modal_heading'] = $heading;

        return $this;
    }

    /**
     * @param $description string|Closure|null
     *
     * @return static
     */
    public function modal_description(string|Closure|null $description): static
    {
        $this->props['modal_description'] = $description;

        return $this;
    }

    /**
     * @param $condition bool
     *
     * @return static
     */
    public function requires_confirmation(bool $condition = true): static
    {
        $this->props['requires_confirmation'] = $condition;

        return $this;
    }

    /**
     * @param $label string|null
     *
     * @return static
     */
    public function modal_submit_label(?string $label): static
    {
        $this->props['modal_submit_label'] = $label;

        return $this;
    }

    /**
     * @param $label string|null
     *
     * @return static
     */
    public function modal_cancel_label(?string $label): static
    {
        $this->props['modal_cancel_label'] = $label;

        return $this;
    }

    /**
     * @param $width string
     *
     * @return static
     */
    public function modal_width(string $width): static
    {
        $this->props['modal_width'] = $width;

        return $this;
    }
}
