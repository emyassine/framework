<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Actions;

use Webkernel\Component\StaticComponent;
use Webkernel\Platform\Components\Concerns\HasIcon;
use Webkernel\Platform\Components\Concerns\HasIconPosition;

/**
 * Dropdown / button group of related actions.
 */
final class ActionGroup extends StaticComponent
{
    use HasIcon;
    use HasIconPosition;

    /** @var list<Action|ActionGroup> */
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
        return 'webkernel::dropdown';
    }

    /**
     * @param $label string
     *
     * @return static
     */
    public function label(string $label): static
    {
        $this->props['label'] = $label;

        return $this;
    }

    /**
     * @param $actions list<Action|ActionGroup>
     *
     * @return static
     */
    public function actions(array $actions): static
    {
        $out = [];
        foreach ($actions as $action) {
            if (! $action instanceof Action && ! $action instanceof ActionGroup) {
                throw new \InvalidArgumentException('ActionGroup::actions() expects Action or ActionGroup instances.');
            }
            $out[] = $action;
        }
        $this->actions = $out;

        return $this;
    }

    /**
     * @return list<Action|ActionGroup>
     */
    public function get_actions(): array
    {
        return $this->actions;
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
        $extra['label'] = (string) ($this->props['label'] ?? $this->name);

        return parent::render($extra);
    }
}
