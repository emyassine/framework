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

final class CreateInvoice extends Page
{
    public const HEADER = 'Create invoice';

    protected static string $slug = 'create';

    /**
     * @param $arguments mixed
     *
     * @return string
     */
    public function __invoke(mixed ...$arguments): string
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $invoice = new Invoice(
                InvoiceStore::next_id(),
                \trim((string) ($_POST['number'] ?? '')),
                \trim((string) ($_POST['customer'] ?? '')),
                \trim((string) ($_POST['total'] ?? '0')),
                \trim((string) ($_POST['status'] ?? 'draft')),
            );
            InvoiceStore::save($invoice);
            \header('Location: /billing/invoices', true, 302);

            return '';
        }

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
        return [
            'action' => '/billing/invoices/create',
            'schema' => InvoiceResource::form(new Schema()),
            'state' => [],
        ];
    }
}
