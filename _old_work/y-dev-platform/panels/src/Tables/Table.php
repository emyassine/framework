<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Tables;

final class Table
{
    /** @var list<array{key: string, label: string}> */
    private array $columns = [];

    /**
     * @param list<array{key: string, label: string}|string> $columns
     */
    public function columns(array $columns): self
    {
        $out = [];
        foreach ($columns as $column) {
            if (\is_string($column)) {
                $out[] = ['key' => $column, 'label' => $column];
                continue;
            }
            if (isset($column['key'])) {
                $out[] = [
                    'key' => (string) $column['key'],
                    'label' => (string) ($column['label'] ?? $column['key']),
                ];
            }
        }
        $this->columns = $out;

        return $this;
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function get_columns(): array
    {
        return $this->columns;
    }
}
