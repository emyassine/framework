<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

/**
 * Responsive grid placement. Same contract as Filament columns / columnSpan.
 *
 * //> Integer columns() and column_span() apply from `lg` up. Smaller viewports stay 1 unless `default` is set.
 *
 * @property array<string, mixed> $props
 */
trait HasLayout
{
    /**
     * @param $columns int|array<string, int>
     *
     * @return static
     */
    public function columns(int|array $columns): static
    {
        $this->props['columns'] = $columns;

        return $this;
    }

    /**
     * @param $span int|string|array<string, int|string>
     *
     * @return static
     */
    public function column_span(int|string|array $span): static
    {
        $this->props['column_span'] = $span;

        return $this;
    }

    /**
     * @return static
     */
    public function column_span_full(): static
    {
        return $this->column_span(['default' => 'full']);
    }

    /**
     * @param $start int|array<string, int>
     *
     * @return static
     */
    public function column_start(int|array $start): static
    {
        $this->props['column_start'] = $start;

        return $this;
    }

    /**
     * @param $order int|array<string, int>
     *
     * @return static
     */
    public function column_order(int|array $order): static
    {
        $this->props['column_order'] = $order;

        return $this;
    }

    /**
     * @param $dense bool
     *
     * @return static
     */
    public function dense(bool $dense = true): static
    {
        $this->props['dense'] = $dense;

        return $this;
    }

    /**
     * @param $gap bool
     *
     * @return static
     */
    public function gap(bool $gap = true): static
    {
        $this->props['gap'] = $gap;

        return $this;
    }

    /**
     * @param $condition bool
     *
     * @return static
     */
    public function grid_container(bool $condition = true): static
    {
        $this->props['grid_container'] = $condition;

        return $this;
    }

    /**
     * @param $grow bool
     *
     * @return static
     */
    public function grow(bool $grow = true): static
    {
        $this->props['grow'] = $grow;

        return $this;
    }

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

    /**
     * @return string
     */
    public function grid_class(): string
    {
        if (! $this->has_columns()) {
            return '';
        }
        $class = ['w-grid'];
        if (($this->props['dense'] ?? false) === true) {
            $class[] = 'w-dense';
        }
        if (($this->props['gap'] ?? true) === false) {
            $class[] = 'w-no-gap';
        }
        if (($this->props['grid_container'] ?? false) === true) {
            $class[] = 'w-grid-container';
        }

        return \implode(' ', $class);
    }

    /**
     * @return string
     */
    public function grid_style(): string
    {
        return $this->css_vars('cols', $this->props['columns'] ?? null, true);
    }

    /**
     * @return string
     */
    public function column_class(): string
    {
        $class = ['w-col'];
        if (($this->props['grow'] ?? null) === false) {
            $class[] = 'w-flex-nogrow';
        } elseif (($this->props['grow'] ?? null) === true) {
            $class[] = 'w-flex-grow';
        }
        foreach ($this->span_full_classes($this->props['column_span'] ?? null) as $name) {
            $class[] = $name;
        }

        return \implode(' ', $class);
    }

    /**
     * @return string
     */
    public function column_style(): string
    {
        $style = $this->css_vars('col-span', $this->props['column_span'] ?? null, true);
        $style .= $this->css_vars('col-start', $this->props['column_start'] ?? null, true);
        $style .= $this->css_vars('col-order', $this->props['column_order'] ?? null, false);

        return \trim($style);
    }

    /**
     * @return array<string, mixed>
     */
    public function extra_attribute_bag(): array
    {
        $bag = [];
        if (isset($this->props['extra_attributes']) && \is_array($this->props['extra_attributes'])) {
            $bag = $this->props['extra_attributes'];
        }
        $class = \trim((string) ($bag['class'] ?? '').' '.$this->column_class());
        if ($class !== '') {
            $bag['class'] = $class;
        }
        $style = \trim((string) ($bag['style'] ?? '').' '.$this->column_style());
        if ($style !== '') {
            $bag['style'] = $style;
        }

        return $bag;
    }

    /**
     * @return bool
     */
    public function has_columns(): bool
    {
        return \array_key_exists('columns', $this->props) && $this->props['columns'] !== null;
    }

    /**
     * @param $prefix string
     * @param $value int|string|array<string, int|string>|null
     * @param $int_is_lg bool
     *
     * @return string
     */
    private function css_vars(string $prefix, mixed $value, bool $int_is_lg): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $map = $this->breakpoint_map($value, $int_is_lg);
        $out = '';
        foreach ($map as $breakpoint => $raw) {
            if ($raw === 'full') {
                continue;
            }
            if (! \is_int($raw) && ! (\is_string($raw) && \ctype_digit($raw))) {
                continue;
            }
            $out .= '--'.$prefix.'-'.$this->var_key($breakpoint).': '.(int) $raw.';';
        }

        return $out;
    }

    /**
     * @param $span int|string|array<string, int|string>|null
     *
     * @return list<string>
     */
    private function span_full_classes(mixed $span): array
    {
        if ($span === null || $span === '') {
            return [];
        }
        $map = $this->breakpoint_map($span, true);
        $out = [];
        foreach ($map as $breakpoint => $raw) {
            if ($raw !== 'full') {
                continue;
            }
            $out[] = $breakpoint === 'default' ? 'w-span-full' : 'w-span-'.$this->var_key($breakpoint).'-full';
        }

        return $out;
    }

    /**
     * @param $value int|string|array<string, int|string>
     * @param $int_is_lg bool
     *
     * @return array<string, int|string>
     */
    private function breakpoint_map(int|string|array $value, bool $int_is_lg): array
    {
        if (\is_int($value) || \is_string($value)) {
            return $int_is_lg
                ? ['default' => \is_string($value) && $value === 'full' ? 1 : 1, 'lg' => $value]
                : ['default' => $value];
        }

        return $value;
    }

    /**
     * @param $breakpoint string
     *
     * @return string
     */
    private function var_key(string $breakpoint): string
    {
        $key = \ltrim($breakpoint, '!');
        if (\str_starts_with($key, '@')) {
            return 'at-'.\substr($key, 1);
        }

        return $key;
    }
}
