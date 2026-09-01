<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Component\Concerns;

/**
 * Trait for managing component props.
 */
trait HasProps
{
    /**
     * @var array<string, mixed>
     */
    protected array $props = [];

    /**
     * Set props for the component.
     *
     * @param array<string, mixed> $props
     * @return static
     */
    public function set_props(array $props): static
    {
        $this->props = array_merge($this->props, $props);
        return $this;
    }

    /**
     * Set a single prop value.
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function set_prop(string $key, mixed $value): static
    {
        $this->props[$key] = $value;
        return $this;
    }

    /**
     * Get a prop value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get_prop(string $key, mixed $default = null): mixed
    {
        return $this->props[$key] ?? $default;
    }

    /**
     * Get all props.
     *
     * @return array<string, mixed>
     */
    public function get_props(): array
    {
        return $this->props;
    }

    /**
     * Get props as array for view.
     *
     * @return array<string, mixed>
     */
    protected function props_to_array(): array
    {
        return $this->props;
    }
}
