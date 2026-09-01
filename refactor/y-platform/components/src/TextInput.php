<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasMethodMake;
use Webkernel\Platform\Components\Concerns\HasLabel;
use Webkernel\Platform\Components\Concerns\HasLayout;

/**
 * Text field. Same view for the tag and schema declaration.
 */
final class TextInput extends \Webkernel\Component\StaticComponent
{
    use HasLayout;
    use HasMethodMake;
    use HasLabel;

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::text-input';
    }

    /**
     * @param $type string
     *
     * @return static
     */
    public function type(string $type): static
    {
        $this->props['type'] = $type;

        return $this;
    }

    /**
     * @param $value string
     *
     * @return static
     */
    public function value(string $value): static
    {
        $this->props['value'] = $value;

        return $this;
    }

    /**
     * @param $hint string
     *
     * @return static
     */
    public function hint(string $hint): static
    {
        $this->props['hint'] = $hint;

        return $this;
    }

    /**
     * @param $error string
     *
     * @return static
     */
    public function error(string $error): static
    {
        $this->props['error'] = $error;

        return $this;
    }
}
