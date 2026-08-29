<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing\Presentation\Resources\Invoices\Pages;

use Acme\Billing\Infrastructure\InvoiceStore;
use Acme\Billing\Presentation\Resources\Invoices\InvoiceResource;
use Webkernel\Platform\Tables\Table;
use Webkernel\View\View;

final class ListInvoices
{
    /**
     * @param list<string> $methods
     * @return array{class: class-string, path: string, methods: list<string>}
     */
    public static function route(string $path, array $methods = ['GET']): array
    {
        return ['class' => self::class, 'path' => $path, 'methods' => $methods];
    }

    public function __invoke(): string
    {
        $table = InvoiceResource::table(new Table());

        return View::make('billing::invoices.index', [
            'title' => 'Invoices',
            'invoices' => InvoiceStore::all(),
            'columns' => $table->get_columns(),
        ])->render();
    }
}
