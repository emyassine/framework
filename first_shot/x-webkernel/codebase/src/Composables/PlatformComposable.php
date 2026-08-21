<?php declare(strict_types=1);

namespace Webkernel\Composables;

final class PlatformComposable implements ComposableContract
{
    public static function api_name(): string
    {
        return 'platform';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    public function instance(): InstanceComposable
    {
        $container = webapp()->container();
        if (! $container->has(InstanceComposable::class)) {
            $container->singleton(InstanceComposable::class);
        }

        /** @var InstanceComposable $instance */
        $instance = $container->get(InstanceComposable::class);

        return $instance;
    }

    public function system_admin(): SystemAdminComposable
    {
        $container = webapp()->container();
        if (! $container->has(SystemAdminComposable::class)) {
            $container->singleton(SystemAdminComposable::class);
        }

        /** @var SystemAdminComposable $admin */
        $admin = $container->get(SystemAdminComposable::class);

        return $admin;
    }

    public function owners(): OwnersComposable
    {
        $container = webapp()->container();
        if (! $container->has(OwnersComposable::class)) {
            $container->singleton(OwnersComposable::class);
        }

        /** @var OwnersComposable $owners */
        $owners = $container->get(OwnersComposable::class);

        return $owners;
    }

    public function telemetry(): TelemetryComposable
    {
        $container = webapp()->container();
        if (! $container->has(TelemetryComposable::class)) {
            $container->singleton(TelemetryComposable::class);
        }

        /** @var TelemetryComposable $telemetry */
        $telemetry = $container->get(TelemetryComposable::class);

        return $telemetry;
    }
}
