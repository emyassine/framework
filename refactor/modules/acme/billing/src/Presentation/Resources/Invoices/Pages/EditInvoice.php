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
use Webkernel\Platform\Pages\Page;
use Webkernel\Platform\Schemas\Schema;

final class EditInvoice extends Page
{
    public const HEADER = 'Edit invoice';

    protected static string $slug = '{record}/edit';

    private ?Invoice $invoice = null;

    /**
     * @param $record string
     *
     * @return string
     */
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

        $this->invoice = $invoice;

        return $this->render();
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'billing::invoices.form';
    }

    /**
     * @return array<string, mixed>
     */
    public function view_data(): array
    {
        $invoice = $this->invoice;
        if ($invoice === null) {
            return [];
        }

        return [
            'action' => '/billing/invoices/'.$invoice->id.'/edit',
            'schema' => InvoiceResource::form(new Schema()),
            'state' => $invoice->to_array(),
        ];
    }
}
