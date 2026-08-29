<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

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
