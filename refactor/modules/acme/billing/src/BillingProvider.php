<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing;

use Acme\Billing\Presentation\BillingPanelProvider;
use Webkernel\PlatformProvider;

final class BillingProvider extends PlatformProvider
{
    public const VIEW_NAMESPACE = 'billing';

    public const VIEWS = [__DIR__.'/../resources/views'];

    public const COMPONENTS = [__DIR__.'/../resources/views/components'];

    public const PANELS = [BillingPanelProvider::class];
}
