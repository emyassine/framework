<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Acme\Billing\Infrastructure;

use Acme\Billing\Domain\Invoice;

/**
 * JSON file store. Upgrade to a real table when billing has concurrent writers.
 */
final class InvoiceStore
{
    /**
     * @return list<Invoice>
     */
    public static function all(): array
    {
        $out = [];
        foreach (self::rows() as $row) {
            $out[] = Invoice::from_array($row);
        }

        return $out;
    }

    public static function find(string $id): ?Invoice
    {
        foreach (self::rows() as $row) {
            if ($row['id'] === $id) {
                return Invoice::from_array($row);
            }
        }

        return null;
    }

    public static function save(Invoice $invoice): Invoice
    {
        $rows = self::rows();
        $found = false;
        foreach ($rows as $i => $row) {
            if ($row['id'] === $invoice->id) {
                $rows[$i] = $invoice->to_array();
                $found = true;
                break;
            }
        }
        if (! $found) {
            $rows[] = $invoice->to_array();
        }
        self::write($rows);

        return $invoice;
    }

    public static function next_id(): string
    {
        return \bin2hex(\random_bytes(4));
    }

    /**
     * @return list<array{id: string, number: string, customer: string, total: string, status: string}>
     */
    private static function rows(): array
    {
        $file = self::path();
        if (! \is_file($file)) {
            return [];
        }
        $raw = \file_get_contents($file);
        $data = \is_string($raw) ? \json_decode($raw, true) : null;
        if (! \is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $row) {
            if (! \is_array($row) || ! isset($row['id'], $row['number'], $row['customer'], $row['total'], $row['status'])) {
                continue;
            }
            $out[] = [
                'id' => (string) $row['id'],
                'number' => (string) $row['number'],
                'customer' => (string) $row['customer'],
                'total' => (string) $row['total'],
                'status' => (string) $row['status'],
            ];
        }

        return $out;
    }

    /**
     * @param list<array<string, string>> $rows
     */
    private static function write(array $rows): void
    {
        $file = self::path();
        $dir = \dirname($file);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }
        $json = \json_encode($rows, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $tmp = $file.'.'.\bin2hex(\random_bytes(4)).'.tmp';
        if (\file_put_contents($tmp, $json, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write '.$tmp);
        }
        if (! \rename($tmp, $file)) {
            @\unlink($tmp);
            throw new \RuntimeException('Unable to rename over '.$file);
        }
    }

    private static function path(): string
    {
        return webapp_path('platform/storage/app/invoices.json');
    }
}
