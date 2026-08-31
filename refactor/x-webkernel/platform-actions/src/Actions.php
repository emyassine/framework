<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Actions;

use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasAlignment;
use Webkernel\Platform\Components\Concerns\HasVerticalAlignment;
use Webkernel\Platform\Components\Enums\Alignment;

/**
 * Row of Action buttons inside a schema.
 */
final class Actions extends StaticComponent
{
    use HasAlignment;
    use HasVerticalAlignment;

    /** @var list<Action> */
    private array $actions = [];

    /**
     * @param $name string
     *
     * @return static
     */
    public static function make(string $name = ''): static
    {
        $self = new static();
        $self->name = $name;

        return $self;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::actions';
    }

    /**
     * @param $actions list<Action>
     *
     * @return static
     */
    public function actions(array $actions): static
    {
        $out = [];
        foreach ($actions as $action) {
            if (! $action instanceof Action) {
                throw new \InvalidArgumentException('Actions::actions() expects Action instances.');
            }
            $out[] = $action;
        }
        $this->actions = $out;

        return $this;
    }

    /**
     * @param $full_width bool
     *
     * @return static
     */
    public function full_width(bool $full_width = true): static
    {
        $this->props['full_width'] = $full_width;

        return $this;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        $slot = '';
        foreach ($this->actions as $action) {
            $slot .= $action->render();
        }
        $extra['slot'] = $slot;
        $extra['full_width'] = ($this->props['full_width'] ?? false) === true;
        $extra['alignment'] = (string) ($this->props['alignment'] ?? Alignment::Start->value);
        $extra['vertical_alignment'] = (string) ($this->props['vertical_alignment'] ?? '');

        return parent::render($extra);
    }
}
