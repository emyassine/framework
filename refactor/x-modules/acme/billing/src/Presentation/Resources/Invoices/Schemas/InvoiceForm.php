<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing\Presentation\Resources\Invoices\Schemas;

use Webkernel\Platform\Components\TextInput;
use Webkernel\Platform\Schemas\Schema;

final class InvoiceForm
{
    /**
     * @param $schema Schema
     *
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')->label('Number'),
            TextInput::make('customer')->label('Customer'),
            TextInput::make('total')->label('Total'),
            TextInput::make('status')->label('Status'),
        ]);
    }
}
