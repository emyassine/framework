<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Webkernel\Platform\AppOwner;

final class OwnersComposable
{
    /** @var array<string|int, AppOwner> */
    private array $owners = [];

    private int|string|null $current_id = null;

    /**
     * @return list<AppOwner>
     */
    public function list(): array
    {
        return array_values($this->owners);
    }

    public function current(): ?AppOwner
    {
        if ($this->current_id === null) {
            return null;
        }

        return $this->owners[$this->current_id] ?? null;
    }

    public function is_owner(int|string $user_id): bool
    {
        return isset($this->owners[$user_id]);
    }

    public function register(AppOwner $owner, bool $current = false): void
    {
        $this->owners[$owner->id] = $owner;
        if ($current) {
            $this->current_id = $owner->id;
        }
    }
}
