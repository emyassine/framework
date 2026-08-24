<?php declare(strict_types=1);

namespace Webkernel\Composables;

final class SystemAdminComposable
{
    /** @var list<string> */
    private array $panels = [];

    public function is_active(): bool
    {
        try {
            $panel = webapp()->panel();

            return $panel->is_platform_panel() && $panel->current()->id === 'platform.system_admin';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function audit_logs(): array
    {
        // ponytail: in-memory empty until a persistence layer exists
        return [];
    }

    public function register_panel(string $panel_class): void
    {
        if (! in_array($panel_class, $this->panels, true)) {
            $this->panels[] = $panel_class;
        }
        webapp()->panel()->register($panel_class, 'platform');
    }
}
