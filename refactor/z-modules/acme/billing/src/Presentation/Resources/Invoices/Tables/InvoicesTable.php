<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing\Presentation\Resources\Invoices\Tables;

use Webkernel\Platform\Tables\Table;

final class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ['key' => 'number', 'label' => 'Number'],
            ['key' => 'customer', 'label' => 'Customer'],
            ['key' => 'total', 'label' => 'Total'],
            ['key' => 'status', 'label' => 'Status'],
        ]);
    }
}
