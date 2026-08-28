<?php declare(strict_types=1);

namespace Acme\Billing;

use Acme\Billing\Presentation\BillingPanelProvider;
use Webkernel\PlatformProvider;

final class BillingProvider extends PlatformProvider
{
    public const VIEWS = [__DIR__.'/../resources/views'];

    public const COMPONENTS = [__DIR__.'/../resources/views/components'];

    public const PANELS = [BillingPanelProvider::class];
}
