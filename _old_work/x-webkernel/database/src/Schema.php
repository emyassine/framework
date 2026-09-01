<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database;

/**
 * Table create / drop. Migrations call this.
 */
final class Schema
{
    /**
     * @param $table string
     * @param $define callable(Blueprint): void
     *
     * @return void
     */
    public static function create(string $table, callable $define): void
    {
        $blueprint = new Blueprint($table, Database::driver());
        $define($blueprint);
        Database::connection()->statement($blueprint->to_sql());
    }

    /**
     * @param $table string
     *
     * @return void
     */
    public static function drop(string $table): void
    {
        Database::connection()->statement(
            'DROP TABLE IF EXISTS '.Database::driver()->quote($table),
        );
    }

    /**
     * @param $table string
     *
     * @return bool
     */
    public static function has_table(string $table): bool
    {
        return Database::connection()->has_table($table);
    }
}
