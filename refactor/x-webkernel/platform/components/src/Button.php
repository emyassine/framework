<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

/**
 * Clickable button or anchor. Same view for the tag and `Button::make()`.
 */
final class Button extends Component
{
    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::button';
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
     * @param $tag 'button'|'a'
     *
     * @return static
     */
    public function tag(string $tag): static
    {
        $this->props['tag'] = $tag;

        return $this;
    }

    /**
     * @param $href string
     *
     * @return static
     */
    public function href(string $href): static
    {
        $this->props['href'] = $href;
        $this->props['tag'] = 'a';

        return $this;
    }

    /**
     * @param $target string
     *
     * @return static
     */
    public function target(string $target): static
    {
        $this->props['target'] = $target;

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
     * @param $ghost bool
     *
     * @return static
     */
    public function ghost(bool $ghost = true): static
    {
        $this->props['ghost'] = $ghost;

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
     * @param $tooltip string
     *
     * @return static
     */
    public function tooltip(string $tooltip, string $placement = 'top'): static
    {
        $this->props['tooltip'] = $tooltip;
        $this->props['tooltip_placement'] = $placement;

        return $this;
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

    /**
     * @param $badge string
     *
     * @return static
     */
    public function badge(string $badge): static
    {
        $this->props['badge'] = $badge;

        return $this;
    }

    /**
     * @param $color string
     *
     * @return static
     */
    public function badge_color(string $color): static
    {
        $this->props['badge_color'] = $color;

        return $this;
    }

    /**
     * @param $form string
     *
     * @return static
     */
    public function form(string $form): static
    {
        $this->props['form'] = $form;

        return $this;
    }
}
