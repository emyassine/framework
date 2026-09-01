<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Schemas;

use Webkernel\Component\Component;
use Webkernel\Platform\Components\Concerns\HasChildSchema;
use Webkernel\Platform\Components\Concerns\HasLayout;
use Webkernel\Platform\Components\Concerns\HasMethodMake;
use Webkernel\Platform\Schemas\Enums\SchemaMode;

/**
 * Layout atom with a nested schema. Grid, Fieldset, Flex share this.
 */
abstract class LayoutComponent extends Component
{
    use HasChildSchema;
    use HasLayout;
    use HasMethodMake;

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
