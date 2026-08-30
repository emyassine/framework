<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Closure;
use Webkernel\Platform\Schemas\Schema;

/**
 * One schema tab. Used inside `Tabs::tabs()`.
 *
 * //> First argument of `make()` is the label, same as Filament `Tab::make()`.
 */
final class Tab extends Component
{
    private Schema $child;

    /**
     * @param $label string
     *
     * @return static
     */
    public static function make(string $label = ''): static
    {
        $self = new static();
        $self->name = $label;
        if ($label !== '') {
            $self->props['label'] = $label;
        }
        $self->child = Schema::make();

        return $self;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::tabs.panel';
    }

    /**
     * @param $components Schema|list<Component>
     *
     * @return static
     */
    public function schema(Schema|array $components): static
    {
        $this->child = $components instanceof Schema
            ? $components
            : Schema::make()->components($components);

        return $this;
    }

    /**
     * @param $icon string
     *
     * @return static
     */
    public function icon(string $icon): static
    {
        $this->props['icon'] = $icon;

        return $this;
    }

    /**
     * @param $position IconPosition|string
     *
     * @return static
     */
    public function icon_position(IconPosition|string $position): static
    {
        $this->props['icon_position'] = $position instanceof IconPosition
            ? $position->value
            : $position;

        return $this;
    }

    /**
     * @param $badge string|int|float|Closure|null
     *
     * @return static
     */
    public function badge(string|int|float|Closure|null $badge): static
    {
        $this->props['badge'] = $badge;

        return $this;
    }

    /**
     * @param $color string|Closure|null
     *
     * @return static
     */
    public function badge_color(string|Closure|null $color): static
    {
        $this->props['badge_color'] = $color;

        return $this;
    }

    /**
     * @param $condition bool|Closure
     *
     * @return static
     */
    public function defer_badge(bool|Closure $condition = true): static
    {
        $this->props['defer_badge'] = $condition;

        return $this;
    }

    /**
     * @param $columns int
     *
     * @return static
     */
    public function columns(int $columns): static
    {
        $this->props['columns'] = $columns;

        return $this;
    }

    /**
     * @param $id string
     *
     * @return static
     */
    public function id(string $id): static
    {
        $this->props['id'] = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function tab_id(): string
    {
        $id = (string) ($this->props['id'] ?? '');
        if ($id !== '') {
            return $id;
        }
        $label = $this->tab_label();
        $slug = \strtolower(\preg_replace('/[^a-zA-Z0-9]+/', '-', $label) ?? $label);

        return \trim($slug, '-');
    }

    /**
     * @return string
     */
    public function tab_label(): string
    {
        $label = $this->props['label'] ?? $this->name;

        return \is_string($label) ? $label : '';
    }

    /**
     * @return Schema
     */
    public function child_schema(): Schema
    {
        return $this->child;
    }

    /**
     * @return string
     */
    public function get_icon(): string
    {
        return (string) ($this->props['icon'] ?? '');
    }

    /**
     * @return IconPosition
     */
    public function get_icon_position(): IconPosition
    {
        $raw = $this->props['icon_position'] ?? IconPosition::Before->value;

        return $raw instanceof IconPosition
            ? $raw
            : (IconPosition::tryFrom((string) $raw) ?? IconPosition::Before);
    }

    /**
     * @return string
     */
    public function get_badge(): string
    {
        $badge = $this->props['badge'] ?? null;
        if ($badge instanceof Closure) {
            $badge = $badge();
        }
        if ($badge === null || $badge === '') {
            return '';
        }

        return \is_scalar($badge) ? (string) $badge : '';
    }

    /**
     * @return string
     */
    public function get_badge_color(): string
    {
        $color = $this->props['badge_color'] ?? 'primary';
        if ($color instanceof Closure) {
            $color = $color();
        }

        return \is_string($color) && $color !== '' ? $color : 'primary';
    }

    /**
     * @return bool
     */
    public function is_badge_deferred(): bool
    {
        $flag = $this->props['defer_badge'] ?? false;
        if ($flag instanceof Closure) {
            $flag = $flag();
        }

        return (bool) $flag;
    }

    /**
     * @return int|null
     */
    public function get_columns(): ?int
    {
        $columns = $this->props['columns'] ?? null;

        return \is_int($columns) && $columns > 0 ? $columns : null;
    }

    /**
     * @return array{component: class-string, view: string, props: array<string, mixed>}
     */
    public function to_array(): array
    {
        $dump = parent::to_array();
        $dump['props']['badge'] = $this->get_badge();
        $dump['props']['badge_color'] = $this->get_badge_color();
        $dump['props']['defer_badge'] = $this->is_badge_deferred();
        $dump['schema'] = $this->child->to_array();

        return $dump;
    }
}
