<?php declare(strict_types=1);

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
