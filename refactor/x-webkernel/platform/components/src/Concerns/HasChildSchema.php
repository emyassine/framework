<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components\Concerns;

use Webkernel\Platform\Components\Component;
use Webkernel\Platform\Schemas\Schema;

/**
 * Nested schema for layout components.
 */
trait HasChildSchema
{
    private ?Schema $child = null;

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
     * @return bool
     */
    public function has_nested_schema(): bool
    {
        return $this->child instanceof Schema;
    }

    /**
     * @return Schema
     */
    public function child_schema(): Schema
    {
        return $this->child ??= Schema::make();
    }

    /**
     * @return list<Schema>
     */
    public function nested_schemas(): array
    {
        return [$this->child_schema()];
    }
}
