<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\Platform\Schemas\Schema;
use Webkernel\Platform\Schemas\SchemaMode;

/**
 * Multi-step layout. View: `<x-webkernel::wizard>`.
 *
 * //> Client steps only. Per-step server validation is not wired.
 */
final class Wizard extends Component
{
    /** @var list<WizardStep> */
    private array $steps = [];

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

        return $self;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::wizard';
    }

    /**
     * @param $steps list<WizardStep>
     *
     * @return static
     */
    public function schema(array $steps): static
    {
        $out = [];
        foreach ($steps as $step) {
            if (! $step instanceof WizardStep) {
                throw new \InvalidArgumentException('Wizard::schema() expects WizardStep instances.');
            }
            $out[] = $step;
        }
        $this->steps = $out;

        return $this;
    }

    /**
     * @return list<Schema>
     */
    public function nested_schemas(): array
    {
        $out = [];
        foreach ($this->steps as $step) {
            $out[] = $step->child_schema();
        }

        return $out;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        $state = \is_array($extra['state'] ?? null) ? $extra['state'] : [];
        $errors = \is_array($extra['errors'] ?? null) ? $extra['errors'] : [];
        $mode = isset($extra['mode']) && \is_string($extra['mode'])
            ? SchemaMode::tryFrom($extra['mode'])
            : null;
        $list = '';
        $panels = '';
        foreach ($this->steps as $i => $step) {
            $id = $step->step_id();
            $active = $i === 0;
            $icon = $step->get_icon();
            $mark = $icon !== ''
                ? Icon::make($icon)->render()
                : '<span class="w-wizard-index">'.($i + 1).'</span>';
            $list .= '<li class="w-wizard-nav-item'.($active ? ' w-active' : '').'" data-wizard-step="'.\e($id).'">'
                .'<button type="button" class="w-wizard-nav-btn" data-wizard-goto="'.\e($id).'">'
                .$mark
                .'<span class="w-wizard-nav-text"><span class="w-wizard-nav-label">'.\e($step->step_label()).'</span>'
                .($step->step_description() !== '' ? '<span class="w-wizard-nav-desc">'.\e($step->step_description()).'</span>' : '')
                .'</span></button></li>';
            $child = $step->child_schema();
            if ($mode instanceof SchemaMode) {
                $child->mode($mode);
            }
            $panels .= WizardStep::make($step->step_label())
                ->extra_attributes([
                    'data-wizard-panel' => $id,
                    'class' => $active ? 'w-wizard-panel w-active' : 'w-wizard-panel',
                ])
                ->slot($child->render_tree($state, $errors))
                ->render();
        }
        $extra['list'] = $list;
        $extra['slot'] = $panels;

        return parent::render($extra);
    }
}
