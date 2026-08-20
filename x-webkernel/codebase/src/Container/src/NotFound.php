<?php declare(strict_types=1);

namespace Webkernel\Container;

use Psr\Container\NotFoundExceptionInterface;

final class NotFound extends \InvalidArgumentException implements NotFoundExceptionInterface
{
    public static function of(string $id): self
    {
        return new self('No container binding for ['.$id.'].');
    }
}
