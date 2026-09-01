<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

/**
 * HTML extra attributes bag on a component root.
 *
 * @property array<string, mixed> $props
 */
trait HasExtraAttributes
{
    /**
     * @param $attributes array<string, mixed>
     * @param $merge bool
     *
     * @return static
     */
    public function extra_attributes(array $attributes, bool $merge = false): static
    {
        if ($merge && isset($this->props['extra_attributes']) && \is_array($this->props['extra_attributes'])) {
            $this->props['extra_attributes'] = \array_merge($this->props['extra_attributes'], $attributes);
        } else {
            $this->props['extra_attributes'] = $attributes;
        }

        return $this;
    }
}
