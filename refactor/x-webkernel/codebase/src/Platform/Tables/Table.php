<?php declare(strict_types=1);

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
            if (is_string($column)) {
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
