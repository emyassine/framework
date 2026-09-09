<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Component\Concerns;

/**
 * Trait for managing component actions.
 */
trait HasActions
{
    /**
     * Fill the component from request data.
     *
     * @param array<string, mixed> $data
     * @return static
     */
    public function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * Reset the component state.
     *
     * @return static
     */
    public function reset(): static
    {
        foreach (get_object_vars($this) as $key => $value) {
            if ($key !== 'id' && $key !== 'name' && !str_starts_with($key, 'props')) {
                unset($this->$key);
            }
        }

        return $this;
    }

    /**
     * Reset a specific property.
     *
     * @param string $property
     * @return static
     */
    public function reset_property(string $property): static
    {
        if (property_exists($this, $property) && $property !== 'id' && $property !== 'name') {
            unset($this->$property);
        }

        return $this;
    }
}
