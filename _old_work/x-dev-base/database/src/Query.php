<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database;

/**
 * Table query. `?` placeholders on every driver.
 */
final class Query
{
    /** @var list<string> */
    private array $wheres = [];

    /** @var list<mixed> */
    private array $bindings = [];

    private ?int $limit = null;

    private string $order = '';

    /**
     * @param $connection Connection
     * @param $table string
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
    ) {
    }

    /**
     * @param $column string
     * @param $operator mixed
     * @param $value mixed
     *
     * @return self
     */
    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        if (\func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = $this->connection->driver()->quote($column).' '.$operator.' ?';
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * @param $limit int
     *
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param $column string
     * @param $direction string
     *
     * @return self
     */
    public function order_by(string $column, string $direction = 'asc'): self
    {
        $dir = \strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $this->order = ' ORDER BY '.$this->connection->driver()->quote($column).' '.$dir;

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function get(): array
    {
        return $this->connection->select($this->select_sql(), $this->bindings);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $previous = $this->limit;
        $this->limit = 1;
        $row = $this->connection->select_one($this->select_sql(), $this->bindings);
        $this->limit = $previous;

        return $row;
    }

    /**
     * @param $values array<string, mixed>
     *
     * @return int
     */
    public function insert(array $values): int
    {
        $driver = $this->connection->driver();
        $columns = [];
        $placeholders = [];
        $bindings = [];
        foreach ($values as $column => $value) {
            $columns[] = $driver->quote((string) $column);
            $placeholders[] = '?';
            $bindings[] = $value;
        }
        $sql = 'INSERT INTO '.$driver->quote($this->table)
            .' ('.\implode(', ', $columns).') VALUES ('.\implode(', ', $placeholders).')';
        $this->connection->statement($sql, $bindings);
        $id = $this->connection->last_insert_id();

        return $id === false || $id === '' ? 0 : (int) $id;
    }

    /**
     * @param $values array<string, mixed>
     *
     * @return int
     */
    public function update(array $values): int
    {
        $driver = $this->connection->driver();
        $sets = [];
        $bindings = [];
        foreach ($values as $column => $value) {
            $sets[] = $driver->quote((string) $column).' = ?';
            $bindings[] = $value;
        }
        $sql = 'UPDATE '.$driver->quote($this->table).' SET '.\implode(', ', $sets).$this->where_sql();

        return $this->connection->affecting($sql, [...$bindings, ...$this->bindings]);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        $sql = 'DELETE FROM '.$this->connection->driver()->quote($this->table).$this->where_sql();

        return $this->connection->affecting($sql, $this->bindings);
    }

    /**
     * @return string
     */
    private function select_sql(): string
    {
        $sql = 'SELECT * FROM '.$this->connection->driver()->quote($this->table).$this->where_sql().$this->order;
        if ($this->limit !== null) {
            $sql .= ' LIMIT '.$this->limit;
        }

        return $sql;
    }

    /**
     * @return string
     */
    private function where_sql(): string
    {
        if ($this->wheres === []) {
            return '';
        }

        return ' WHERE '.\implode(' AND ', $this->wheres);
    }
}
