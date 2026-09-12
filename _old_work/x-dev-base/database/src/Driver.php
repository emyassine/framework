<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database;

/**
 * SQL dialect. ClickHouse is named so the DSN does not pretend to be ready.
 */
enum Driver: string
{
    case Sqlite = 'sqlite';
    case Mysql = 'mysql';
    case Mariadb = 'mariadb';
    case Pgsql = 'pgsql';
    case Clickhouse = 'clickhouse';

    /**
     * @param $value string
     *
     * @return self
     */
    public static function from_name(string $value): self
    {
        return self::from(\strtolower($value));
    }

    /**
     * @return bool
     */
    public function is_ready(): bool
    {
        return $this !== self::Clickhouse;
    }

    /**
     * @param $name string
     *
     * @return string
     */
    public function quote(string $name): string
    {
        $wrap = $this === self::Mysql || $this === self::Mariadb ? '`' : '"';

        return $wrap.\str_replace($wrap, $wrap.$wrap, $name).$wrap;
    }

    /**
     * @param $name string
     *
     * @return string
     */
    public function increments(string $name): string
    {
        $col = $this->quote($name);

        return match ($this) {
            self::Sqlite => $col.' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL',
            self::Pgsql => $col.' SERIAL PRIMARY KEY',
            self::Mysql, self::Mariadb => $col.' INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            self::Clickhouse => throw self::not_ready(),
        };
    }

    /**
     * @param $name string
     * @param $length int
     *
     * @return string
     */
    public function varchar(string $name, int $length = 255): string
    {
        $col = $this->quote($name);

        return match ($this) {
            self::Sqlite => $col.' TEXT NOT NULL',
            self::Pgsql, self::Mysql, self::Mariadb => $col.' VARCHAR('.$length.') NOT NULL',
            self::Clickhouse => throw self::not_ready(),
        };
    }

    /**
     * @param $name string
     *
     * @return string
     */
    public function text(string $name): string
    {
        return $this->quote($name).' TEXT NOT NULL';
    }

    /**
     * @param $name string
     *
     * @return string
     */
    public function integer(string $name): string
    {
        $col = $this->quote($name);

        return match ($this) {
            self::Sqlite, self::Pgsql => $col.' INTEGER NOT NULL',
            self::Mysql, self::Mariadb => $col.' INT NOT NULL',
            self::Clickhouse => throw self::not_ready(),
        };
    }

    /**
     * @return string
     */
    public function timestamps(): string
    {
        $created = $this->quote('created_at');
        $updated = $this->quote('updated_at');

        return match ($this) {
            self::Sqlite => $created.' TEXT NULL, '.$updated.' TEXT NULL',
            self::Pgsql, self::Mysql, self::Mariadb => $created.' TIMESTAMP NULL, '.$updated.' TIMESTAMP NULL',
            self::Clickhouse => throw self::not_ready(),
        };
    }

    /**
     * @return \RuntimeException
     */
    public static function not_ready(): \RuntimeException
    {
        return new \RuntimeException(
            'ClickHouse is a named driver, not a connection. Wire the client when analytics lands.',
        );
    }
}
