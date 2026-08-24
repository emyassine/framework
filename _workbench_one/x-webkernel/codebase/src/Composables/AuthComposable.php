<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Webkernel\Auth\UserInterface;

final class AuthComposable implements ComposableContract
{
    private ?UserInterface $user = null;

    public static function api_name(): string
    {
        return 'auth';
    }

    public static function container_lifetime(): string
    {
        return 'scoped';
    }

    public function user(): ?UserInterface
    {
        return $this->user;
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function id(): string|int|null
    {
        return $this->user?->id();
    }

    public function login(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function logout(): void
    {
        $this->user = null;
    }
}
