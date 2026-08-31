<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing\Presentation\Resources\Invoices;

use Acme\Billing\Domain\Invoice;
use Acme\Billing\Presentation\Resources\Invoices\Pages\CreateInvoice;
use Acme\Billing\Presentation\Resources\Invoices\Pages\EditInvoice;
use Acme\Billing\Presentation\Resources\Invoices\Pages\ListInvoices;
use Acme\Billing\Presentation\Resources\Invoices\Schemas\InvoiceForm;
use Acme\Billing\Presentation\Resources\Invoices\Tables\InvoicesTable;
use Webkernel\Platform\Resources\Resource;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\Platform\Tables\Table;

final class InvoiceResource extends Resource
{
    protected static string $model = Invoice::class;

    public static string $slug = 'invoices';

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    /**
     * @return array<string, array{class: class-string, path: string, methods: list<string>}>
     */
    public static function pages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create', ['GET', 'POST']),
            'edit' => EditInvoice::route('/{record}/edit', ['GET', 'POST']),
        ];
    }
}
