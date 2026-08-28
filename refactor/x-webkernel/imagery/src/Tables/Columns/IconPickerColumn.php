<?php

declare(strict_types=1);

namespace Webkernel\Imagery\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;
use Webkernel\Imagery\Concerns\HasIconAnimation;
use Webkernel\Imagery\Concerns\HasIconColor;
use Webkernel\Imagery\Concerns\HasIconSize;

class IconPickerColumn extends Column
{
    use HasIconAnimation;
    use HasIconColor;
    use HasIconSize;

    protected string $view = 'imagery::tables.columns.icon-column';

    protected bool|Closure $showLabel = false;

    protected string|Closure|null $icon = null;

    /**
     * Set a fixed icon (useful when not using a database column).
     */
    public function icon(string|Closure|null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->evaluate($this->icon);
    }

    /**
     * Get the state - returns the fixed icon if set, otherwise the database value.
     */
    public function getState(): mixed
    {
        $icon = $this->getIcon();

        if ($icon !== null) {
            return $icon;
        }

        return parent::getState();
    }

    public function showLabel(bool|Closure $show = true): static
    {
        $this->showLabel = $show;

        return $this;
    }

    public function shouldShowLabel(): bool
    {
        return (bool) $this->evaluate($this->showLabel);
    }
}
