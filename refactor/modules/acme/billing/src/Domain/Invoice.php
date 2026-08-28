<?php declare(strict_types=1);

namespace Acme\Billing\Domain;

/**
 * Invoice row. Persistence is a JSON file, not an ORM.
 *
 * @phpstan-type InvoiceRow array{id: string, number: string, customer: string, total: string, status: string}
 */
final readonly class Invoice
{
    public function __construct(
        public string $id,
        public string $number,
        public string $customer,
        public string $total,
        public string $status,
    ) {
    }

    /**
     * @param InvoiceRow $row
     */
    public static function from_array(array $row): self
    {
        return new self(
            $row['id'],
            $row['number'],
            $row['customer'],
            $row['total'],
            $row['status'],
        );
    }

    /**
     * @return InvoiceRow
     */
    public function to_array(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'customer' => $this->customer,
            'total' => $this->total,
            'status' => $this->status,
        ];
    }
}
