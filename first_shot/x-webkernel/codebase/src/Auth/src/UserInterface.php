<?php declare(strict_types=1);

namespace Webkernel\Auth;

interface UserInterface
{
    public function id(): int|string;

    public function has_role(string $role): bool;
}
