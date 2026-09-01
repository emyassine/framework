<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database;

/**
 * One PDO connection. Query and schema talk only to this.
 */
final class Connection
{
    /**
     * @param $pdo \PDO
     * @param $driver Driver
     * @param $name string
     */
    public function __construct(
        private readonly \PDO $pdo,
        private readonly Driver $driver,
        private readonly string $name = 'default',
    ) {
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * @return Driver
     */
    public function driver(): Driver
    {
        return $this->driver;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return \PDO
     */
    public function pdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param $table string
     *
     * @return Query
     */
    public function table(string $table): Query
    {
        return new Query($this, $table);
    }

    /**
     * @param $sql string
     * @param $bindings list<mixed>
     *
     * @return list<array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        $statement = $this->run($sql, $bindings);
        $rows = $statement->fetchAll();

        return \is_array($rows) ? $rows : [];
    }

    /**
     * @param $sql string
     * @param $bindings list<mixed>
     *
     * @return array<string, mixed>|null
     */
    public function select_one(string $sql, array $bindings = []): ?array
    {
        $statement = $this->run($sql, $bindings);
        $row = $statement->fetch();

        return \is_array($row) ? $row : null;
    }

    /**
     * @param $sql string
     * @param $bindings list<mixed>
     *
     * @return int
     */
    public function affecting(string $sql, array $bindings = []): int
    {
        return $this->run($sql, $bindings)->rowCount();
    }

    /**
     * @param $sql string
     * @param $bindings list<mixed>
     *
     * @return bool
     */
    public function statement(string $sql, array $bindings = []): bool
    {
        $this->run($sql, $bindings);

        return true;
    }

    /**
     * @return string|false
     */
    public function last_insert_id(): string|false
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param $table string
     *
     * @return bool
     */
    public function has_table(string $table): bool
    {
        $name = $this->driver->quote($table);

        try {
            $this->pdo->query('SELECT 1 FROM '.$name.' LIMIT 1');
        } catch (\PDOException) {
            return false;
        }

        return true;
    }

    /**
     * @param $callback callable(): mixed
     *
     * @return mixed
     */
    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback();
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param $sql string
     * @param $bindings list<mixed>
     *
     * @return \PDOStatement
     */
    private function run(string $sql, array $bindings): \PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);

        return $statement;
    }
}
