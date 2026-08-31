<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;

use Webkernel\Platform\Components\Concerns\HasChildSchema;

/**
 * One wizard step. Used inside `Wizard::schema()`.
 */
final class WizardStep extends \Webkernel\Component\StaticComponent
{
    use HasChildSchema;

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
        return 'webkernel::wizard.step';
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
     * @return string
     */
    public function step_id(): string
    {
        $id = (string) ($this->props['id'] ?? '');
        if ($id !== '') {
            return $id;
        }
        $label = $this->step_label();
        $slug = \strtolower(\preg_replace('/[^a-zA-Z0-9]+/', '-', $label) ?? $label);

        return \trim($slug, '-');
    }

    /**
     * @return string
     */
    public function step_label(): string
    {
        $label = $this->props['label'] ?? $this->name;

        return \is_string($label) ? $label : '';
    }

    /**
     * @return string
     */
    public function step_description(): string
    {
        return (string) ($this->props['description'] ?? '');
    }

    /**
     * @return string
     */
    public function get_icon(): string
    {
        return (string) ($this->props['icon'] ?? '');
    }
}
