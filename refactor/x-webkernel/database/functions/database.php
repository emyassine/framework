<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

use Webkernel\Database\Connection;
use Webkernel\Database\Database;
use Webkernel\Database\Query;

if (! \function_exists('db')) {
    /**
     * @param $name string|null
     *
     * @return Connection
     */
    function db(?string $name = null): Connection
    {
        return Database::connection($name);
    }
}

if (! \function_exists('db_table')) {
    /**
     * @param $table string
     *
     * @return Query
     */
    function db_table(string $table): Query
    {
        return Database::table($table);
    }
}
