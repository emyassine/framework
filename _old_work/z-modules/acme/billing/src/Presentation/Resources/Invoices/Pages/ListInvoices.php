<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing\Presentation\Resources\Invoices\Pages;

use Acme\Billing\Infrastructure\InvoiceStore;
use Acme\Billing\Presentation\Resources\Invoices\InvoiceResource;
use Webkernel\Platform\Components\Button;
use Webkernel\Platform\Pages\Page;
use Webkernel\Platform\Tables\Table;

final class ListInvoices extends Page
{
    public const HEADER = 'Invoices';

    protected static string $slug = '/';

    /**
     * @return list<string>
     */
    public function get_header_actions(): array
    {
        return [
            Button::make()->href('/billing/invoices/create')->color('primary')->slot('Create invoice')->render(),
        ];
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'billing::invoices.index';
    }

    /**
     * @return array<string, mixed>
     */
    public function view_data(): array
    {
        $table = InvoiceResource::table(new Table());

        return [
            'invoices' => InvoiceStore::all(),
            'columns' => $table->get_columns(),
        ];
    }
}
