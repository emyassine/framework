<?php declare(strict_types=1);

namespace Webkernel\Container\Tests;

use PHPUnit\Framework\TestCase;
use Webkernel\Container\Attributes\Singleton;
use Webkernel\Container\Events\ModuleBorderViolationAttempted;
use Webkernel\Container\Events\ServiceResolved;
use Webkernel\Container\Exceptions\ModuleBorderViolationException;
use Webkernel\Container\GenericContainer;

final class ContainerSmokeTest extends TestCase
{
    public function testItResolvesSingletonsAndAuditsResolution(): void
    {
        $container = new GenericContainer();

        $first = $container->make(SmokeSingleton::class);
        $second = $container->make(SmokeSingleton::class);

        self::assertSame($first, $second);
        self::assertTrue($container->resolved(SmokeSingleton::class));
        self::assertContainsOnlyInstancesOf(ServiceResolved::class, \array_filter(
            $container->inspector()->auditLog(),
            static fn(object $event): bool => $event instanceof ServiceResolved,
        ));
    }

    public function testModuleScopeBlocksNonExports(): void
    {
        $container = new GenericContainer();
        $container->mountModule('vendor/blog', [SmokePublicContract::class]);

        $this->expectException(ModuleBorderViolationException::class);

        try {
            $container->forModule('vendor/blog')->resolve(SmokeInternalService::class);
        } finally {
            self::assertContainsOnlyInstancesOf(ModuleBorderViolationAttempted::class, \array_filter(
                $container->inspector()->auditLog(),
                static fn(object $event): bool => $event instanceof ModuleBorderViolationAttempted,
            ));
        }
    }
}

#[Singleton]
final class SmokeSingleton {}

interface SmokePublicContract {}

final class SmokeInternalService {}
