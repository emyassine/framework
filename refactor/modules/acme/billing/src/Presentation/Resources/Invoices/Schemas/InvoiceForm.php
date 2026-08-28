<?php declare(strict_types=1);

namespace Acme\Billing\Presentation\Resources\Invoices\Schemas;

use Webkernel\Platform\Schemas\Schema;

final class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->fields([
            ['name' => 'number', 'label' => 'Number'],
            ['name' => 'customer', 'label' => 'Customer'],
            ['name' => 'total', 'label' => 'Total'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }
}
