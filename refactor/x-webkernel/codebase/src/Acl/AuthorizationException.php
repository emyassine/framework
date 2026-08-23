<?php declare(strict_types=1);

namespace Webkernel\Acl;

final class AuthorizationException extends \RuntimeException
{
    public static function denied(string $permission): self
    {
        return new self('Unauthorized: '. $permission);
    }
}
