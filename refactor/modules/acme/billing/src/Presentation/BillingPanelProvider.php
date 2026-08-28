<?php declare(strict_types=1);

namespace Acme\Billing\Presentation;

use Acme\Billing\Presentation\Resources\Invoices\InvoiceResource;
use Webkernel\Platform\Panel;
use Webkernel\Platform\PanelProvider;

final class BillingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->apply_platform_config($panel)
            ->id('billing')
            ->path('billing')
            ->scope('module')
            ->pages([])
            ->resources([
                InvoiceResource::class,
            ]);
    }
}
