<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View;

/**
 * Leftover HTML attributes on a View component after `@props`.
 *
 * //> `__toString()` is already escaped. `View::echo` must not escape it again.
 */
final class AttributeBag implements \Stringable
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private array $attributes = [],
    ) {
    }

    /**
     * @param $attributes array<string, mixed>
     *
     * @return self
     */
    public function merge(array $attributes): self
    {
        $clone = clone $this;
        foreach ($attributes as $key => $value) {
            if ($key === 'class') {
                $clone->attributes['class'] = \trim(
                    (string) ($clone->attributes['class'] ?? '').' '.$this->class_string($value),
                );

                continue;
            }
            $clone->attributes[$key] = $value;
        }

        return $clone;
    }

    /**
     * @param $class string|array<int|string, mixed>
     *
     * @return self
     */
    public function class(string|array $class): self
    {
        return $this->merge(['class' => $class]);
    }

    /**
     * @param $keys list<string>
     *
     * @return self
     */
    public function except(array $keys): self
    {
        $clone = clone $this;
        foreach ($keys as $key) {
            unset($clone->attributes[$key]);
        }

        return $clone;
    }

    /**
     * @param $key string
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->attributes);
    }

    /**
     * @param $key string
     * @param $default mixed
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function to_array(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $html = [];
        foreach ($this->attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $attr = \str_replace('_', '-', (string) $key);
            if ($value === true) {
                $html[] = $attr;

                continue;
            }
            $html[] = $attr.'="'.\e($this->class_string($value)).'"';
        }

        return $html === [] ? '' : ' '.\implode(' ', $html);
    }

    /**
     * @param $value mixed
     *
     * @return string
     */
    private function class_string(mixed $value): string
    {
        if (\is_string($value)) {
            return $value;
        }
        if (! \is_array($value)) {
            return $value === null || $value === false ? '' : (string) $value;
        }
        $out = [];
        foreach ($value as $name => $on) {
            if (\is_int($name)) {
                if (\is_string($on) && $on !== '') {
                    $out[] = $on;
                }

                continue;
            }
            if ($on) {
                $out[] = (string) $name;
            }
        }

        return \implode(' ', $out);
    }
}
