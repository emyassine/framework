<?php declare(strict_types=1);

namespace Webkernel\Container;

use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}
