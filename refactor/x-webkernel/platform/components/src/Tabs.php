<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Platform\Schemas\Schema;

/**
 * Tab list plus panels. Same view for the tag and `Tabs::make()`.
 */
final class Tabs extends Component
{
    /** @var list<array{id: string, label: string, icon?: string, schema: Schema}> */
    private array $tabs = [];

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::tabs';
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
     * @param $vertical bool
     *
     * @return static
     */
    public function vertical(bool $vertical = true): static
    {
        $this->props['vertical'] = $vertical;

        return $this;
    }

    /**
     * @param $html string
     *
     * @return static
     */
    public function list(string $html): static
    {
        $this->props['list'] = $html;

        return $this;
    }

    /**
     * @param $tabs list<array{id: string, label: string, icon?: string, schema: Schema}>
     *
     * @return static
     */
    public function tabs(array $tabs): static
    {
        $this->tabs = $tabs;

        return $this;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        if ($this->tabs !== []) {
            /** @var array<string, mixed> $state */
            $state = \is_array($extra['state'] ?? null) ? $extra['state'] : $extra;
            /** @var array<string, string> $errors */
            $errors = \is_array($extra['errors'] ?? null) ? $extra['errors'] : [];
            $list = '';
            $panels = '';
            foreach ($this->tabs as $i => $tab) {
                $list .= TabsItem::make()
                    ->tab($tab['id'])
                    ->active($i === 0)
                    ->icon((string) ($tab['icon'] ?? ''))
                    ->slot($tab['label'])
                    ->render();
                $body = $tab['schema']->render($state, $errors);
                $panels .= TabsPanel::make()
                    ->tab($tab['id'])
                    ->active($i === 0)
                    ->slot($body)
                    ->render();
            }
            $extra['list'] = $list;
            $extra['slot'] = $panels;
        }

        return parent::render($extra);
    }
}
