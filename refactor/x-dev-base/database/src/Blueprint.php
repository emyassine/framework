<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database;

/**
 * Create-table column list. One job: emit SQL for Schema::create().
 */
final class Blueprint
{
    /** @var list<string> */
    private array $columns = [];

    /** @var list<string> */
    private array $indexes = [];

    /**
     * @param $table string
     * @param $driver Driver
     */
    public function __construct(
        private readonly string $table,
        private readonly Driver $driver,
    ) {
    }

    /**
     * @param $name string
     *
     * @return self
     */
    public function increments(string $name = 'id'): self
    {
        $this->columns[] = $this->driver->increments($name);

        return $this;
    }

    /**
     * @param $name string
     * @param $length int
     *
     * @return self
     */
    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = $this->driver->varchar($name, $length);

        return $this;
    }

    /**
     * @param $name string
     *
     * @return self
     */
    public function text(string $name): self
    {
        $this->columns[] = $this->driver->text($name);

        return $this;
    }

    /**
     * @param $name string
     *
     * @return self
     */
    public function integer(string $name): self
    {
        $this->columns[] = $this->driver->integer($name);

        return $this;
    }

    /**
     * @return self
     */
    public function timestamps(): self
    {
        $this->columns[] = $this->driver->timestamps();

        return $this;
    }

    /**
     * @param $column string
     *
     * @return self
     */
    public function unique(string $column): self
    {
        $this->indexes[] = 'UNIQUE ('.$this->driver->quote($column).')';

        return $this;
    }

    /**
     * @return string
     */
    public function to_sql(): string
    {
        $parts = [...$this->columns, ...$this->indexes];

        return 'CREATE TABLE '.$this->driver->quote($this->table).' ('.\implode(', ', $parts).')';
    }
}
