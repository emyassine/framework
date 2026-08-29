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

final class CreateInvoice
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

        $form = InvoiceResource::form(new Schema());

        return View::make('billing::invoices.form', [
            'title' => 'Create invoice',
            'action' => '/billing/invoices/create',
            'invoice' => null,
            'fields' => $form->get_fields(),
        ])->render();
    }
}
