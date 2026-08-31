<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;
use Webkernel\Component\StaticComponent;

use Closure;
use Webkernel\View\Htmlable;

/**
 * Schema / page action. Renders as a Button. Callbacks run from the Page POST.
 *
 * //> Modal confirmation is not wired. Upgrade when an action needs a confirm dialog.
 */
final class Action extends \Webkernel\Component\StaticComponent implements Htmlable, \Stringable
{
    private mixed $callback = null;

    /**
     * @param $name string
     *
     * @return static
     */
    public static function make(string $name = ''): static
    {
        $self = new static();
        $self->name = $name;
        $self->props['type'] = 'button';
        $self->props['tag'] = 'button';

        return $self;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::button';
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
     * @param $color string
     *
     * @return static
     */
    public function color(string $color): static
    {
        $this->props['color'] = $color;

        return $this;
    }

    /**
     * @param $size Size|string
     *
     * @return static
     */
    public function size(Size|string $size): static
    {
        $this->props['size'] = $size instanceof Size ? $size->value : $size;

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
     * @param $position IconPosition|string
     *
     * @return static
     */
    public function icon_position(IconPosition|string $position): static
    {
        $this->props['icon_position'] = $position instanceof IconPosition
            ? $position->value
            : $position;

        return $this;
    }

    /**
     * @param $url string|Closure
     *
     * @return static
     */
    public function url(string|Closure $url): static
    {
        $this->props['url'] = $url;
        $this->props['tag'] = 'a';

        return $this;
    }

    /**
     * @return static
     */
    public function button(): static
    {
        $this->props['style'] = 'button';

        return $this;
    }

    /**
     * @return static
     */
    public function link(): static
    {
        $this->props['ghost'] = true;
        $this->props['style'] = 'link';

        return $this;
    }

    /**
     * @return static
     */
    public function icon_button(): static
    {
        $this->props['icon_button'] = true;

        return $this;
    }

    /**
     * @param $outlined bool
     *
     * @return static
     */
    public function outlined(bool $outlined = true): static
    {
        $this->props['outlined'] = $outlined;

        return $this;
    }

    /**
     * @param $condition bool
     *
     * @return static
     */
    public function submit(bool $condition = true): static
    {
        if ($condition) {
            $this->props['type'] = 'submit';
        }

        return $this;
    }

    /**
     * @param $callback Closure
     *
     * @return static
     */
    public function action(Closure $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @param $visible bool|Closure
     *
     * @return static
     */
    public function visible(bool|Closure $visible): static
    {
        $this->props['visible'] = $visible;

        return $this;
    }

    /**
     * @param $hidden bool|Closure
     *
     * @return static
     */
    public function hidden(bool|Closure $hidden = true): static
    {
        $this->props['hidden'] = $hidden;

        return $this;
    }

    /**
     * @param $disabled bool|Closure
     *
     * @return static
     */
    public function disabled(bool|Closure $disabled = true): static
    {
        $this->props['disabled'] = $disabled;

        return $this;
    }

    /**
     * @return Closure|null
     */
    public function callback(): ?Closure
    {
        return $this->callback instanceof Closure ? $this->callback : null;
    }

    /**
     * @return string
     */
    public function action_name(): string
    {
        return $this->name;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        if ($this->is_hidden()) {
            return '';
        }
        $url = $this->props['url'] ?? null;
        if ($url instanceof Closure) {
            $url = $url();
        }
        $label = $this->label_text();
        $icon_only = ($this->props['icon_button'] ?? false) === true;
        $extra['type'] = (string) ($this->props['type'] ?? 'button');
        $extra['tag'] = isset($this->props['url']) ? 'a' : 'button';
        $extra['href'] = \is_string($url) ? $url : null;
        $extra['color'] = (string) ($this->props['color'] ?? 'primary');
        $extra['size'] = (string) ($this->props['size'] ?? 'md');
        $extra['icon'] = (string) ($this->props['icon'] ?? '');
        $extra['icon_position'] = (string) ($this->props['icon_position'] ?? IconPosition::Before->value);
        $extra['outlined'] = ($this->props['outlined'] ?? false) === true;
        $extra['ghost'] = ($this->props['ghost'] ?? false) === true;
        $extra['disabled'] = $this->is_disabled();
        $extra['slot'] = $icon_only ? '' : $label;
        if ($icon_only) {
            $class = \trim((string) (($this->props['extra_attributes']['class'] ?? '')).' w-icon-btn');
            $this->extra_attributes(['class' => $class], merge: true);
        }
        if ($this->callback instanceof Closure) {
            $extra['name'] = '_action';
            $extra['value'] = $this->name;
            $extra['type'] = 'submit';
        } elseif (($this->props['type'] ?? '') === 'submit') {
            $extra['name'] = $this->name !== '' ? $this->name : 'submit';
        }

        return parent::render($extra);
    }

    /**
     * @return string
     */
    public function to_html(): string
    {
        return $this->render();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->to_html();
    }

    /**
     * @return string
     */
    private function label_text(): string
    {
        $label = $this->props['label'] ?? null;
        if (\is_string($label) && $label !== '') {
            return $label;
        }
        $name = $this->name;
        $spaced = \preg_replace('/[_-]+/', ' ', $name);

        return \is_string($spaced) ? \ucfirst($spaced) : $name;
    }

    /**
     * @return bool
     */
    private function is_hidden(): bool
    {
        $hidden = $this->props['hidden'] ?? false;
        if ($hidden instanceof Closure) {
            $hidden = $hidden();
        }
        if ((bool) $hidden) {
            return true;
        }
        $visible = $this->props['visible'] ?? true;
        if ($visible instanceof Closure) {
            $visible = $visible();
        }

        return ! (bool) $visible;
    }

    /**
     * @return bool
     */
    private function is_disabled(): bool
    {
        $disabled = $this->props['disabled'] ?? false;
        if ($disabled instanceof Closure) {
            $disabled = $disabled();
        }

        return (bool) $disabled;
    }
}
