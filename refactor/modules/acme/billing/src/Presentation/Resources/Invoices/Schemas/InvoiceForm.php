<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

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
