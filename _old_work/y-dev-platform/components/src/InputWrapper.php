<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasPrefix;
use Webkernel\Platform\Components\Concerns\HasPrefixIcon;
use Webkernel\Platform\Components\Concerns\HasSuffix;
use Webkernel\Platform\Components\Concerns\HasSuffixIcon;
use Webkernel\Platform\Components\Concerns\HasMethodMake;

/**
 * Ring around an input. View: `<x-webkernel::input.wrapper>`.
 */
final class InputWrapper extends StaticComponent
{
    use HasMethodMake;

    use HasPrefix;
    use HasPrefixIcon;
    use HasSuffix;
    use HasSuffixIcon;

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::input.wrapper';
    }

    /**
     * @param $disabled bool
     *
     * @return static
     */
    public function disabled(bool $disabled = true): static
    {
        $this->props['disabled'] = $disabled;

        return $this;
    }
}
