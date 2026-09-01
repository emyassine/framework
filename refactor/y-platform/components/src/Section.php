<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasChildSchema;
use Webkernel\Platform\Components\Concerns\HasIcon;
use Webkernel\Platform\Components\Concerns\HasLayout;
use Webkernel\Platform\Schemas\Enums\SchemaMode;

/**
 * Card around a nested schema. View: `<x-webkernel::section>`.
 *
 * //> Lives in components (UI atom), not in schemas.
 */
final class Section extends StaticComponent
{
    use HasChildSchema;
    use HasIcon;
    use HasLayout;

    /**
     * @param $heading string
     *
     * @return static
     */
    public static function make(string $heading = ''): static
    {
        $self = new static();
        $self->name = $heading;
        if ($heading !== '') {
            $self->props['heading'] = $heading;
        }

        return $self;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::section';
    }

    /**
     * @param $heading string
     *
     * @return static
     */
    public function heading(string $heading): static
    {
        $this->props['heading'] = $heading;

        return $this;
    }

    /**
     * @param $description string
     *
     * @return static
     */
    public function description(string $description): static
    {
        $this->props['description'] = $description;

        return $this;
    }

    /**
     * @param $contained bool
     *
     * @return static
     */
    public function contained(bool $contained = true): static
    {
        $this->props['contained'] = $contained;

        return $this;
    }

    /**
     * @param $compact bool
     *
     * @return static
     */
    public function compact(bool $compact = true): static
    {
        $this->props['compact'] = $compact;

        return $this;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        if (! \array_key_exists('slot', $extra) && ! isset($this->props['slot']) && $this->has_nested_schema()) {
            $state = \is_array($extra['state'] ?? null) ? $extra['state'] : [];
            $errors = \is_array($extra['errors'] ?? null) ? $extra['errors'] : [];
            $mode = isset($extra['mode']) && \is_string($extra['mode'])
                ? SchemaMode::tryFrom($extra['mode'])
                : null;
            $child = $this->child_schema();
            if ($mode instanceof SchemaMode) {
                $child->mode($mode);
            }
            $extra['slot'] = $child->render_tree($state, $errors);
        }
        $extra['heading'] = (string) ($this->props['heading'] ?? $this->name);
        $extra['description'] = (string) ($this->props['description'] ?? '');
        $extra['icon'] = (string) ($this->props['icon'] ?? '');
        $extra['contained'] = ($this->props['contained'] ?? true) !== false;
        $extra['compact'] = ($this->props['compact'] ?? false) === true;
        $extra['grid_class'] = $this->grid_class();
        $extra['grid_style'] = $this->grid_style();
        $extra['dense'] = ($this->props['dense'] ?? false) === true;
        $extra['gap'] = ($this->props['gap'] ?? true) !== false;
        $extra['grid_container'] = ($this->props['grid_container'] ?? false) === true;

        return parent::render($extra);
    }

    /**
     * @return array{component: class-string, view: string, props: array<string, mixed>, schema: array{mode: string, components: list<array<string, mixed>>}}
     */
    public function to_array(): array
    {
        $dump = parent::to_array();
        $dump['schema'] = $this->child_schema()->to_array();

        return $dump;
    }
}
