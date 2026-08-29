<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing\Presentation\Resources\Invoices\Pages;

use Acme\Billing\Domain\Invoice;
use Acme\Billing\Infrastructure\InvoiceStore;
use Acme\Billing\Presentation\Resources\Invoices\InvoiceResource;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\View\View;

final class EditInvoice
{
    /**
     * @param list<string> $methods
     * @return array{class: class-string, path: string, methods: list<string>}
     */
    public static function route(string $path, array $methods = ['GET']): array
    {
        return ['class' => self::class, 'path' => $path, 'methods' => $methods];
    }

    public function __invoke(string $record): string
    {
        $invoice = InvoiceStore::find($record);
        if ($invoice === null) {
            \http_response_code(404);

            return 'Invoice not found';
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $updated = new Invoice(
                $invoice->id,
                \trim((string) ($_POST['number'] ?? $invoice->number)),
                \trim((string) ($_POST['customer'] ?? $invoice->customer)),
                \trim((string) ($_POST['total'] ?? $invoice->total)),
                \trim((string) ($_POST['status'] ?? $invoice->status)),
            );
            InvoiceStore::save($updated);
            \header('Location: /billing/invoices', true, 302);

            return '';
        }

        $schema = InvoiceResource::form(new Schema());

        return View::make('billing::invoices.form', [
            'title' => 'Edit invoice',
            'action' => '/billing/invoices/'.$invoice->id.'/edit',
            'schema' => $schema,
            'state' => $invoice->to_array(),
        ])->render();
    }
}
