<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database;

use Webkernel\Composables\ComposableContract;
use Webkernel\Config\Config;

/**
 * Connection manager. `webapp()->database()`.
 */
final class Database implements ComposableContract
{
    /** @var array<string, Connection> */
    private static array $connections = [];

    private static string $default = 'default';

    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'database';
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$connections = [];
        self::$default = 'default';
        Migrator::flush();
    }

    /**
     * Bind a live connection. Tests use this for sqlite `:memory:`.
     *
     * @param $connection Connection
     * @param $name string|null
     *
     * @return Connection
     */
    public static function use(Connection $connection, ?string $name = null): Connection
    {
        $name ??= $connection->name();
        self::$connections[$name] = $connection;
        self::$default = $name;

        return $connection;
    }

    /**
     * @param $config array<string, mixed>
     * @param $name string
     *
     * @return Connection
     */
    public static function connect(array $config, string $name = 'default'): Connection
    {
        return self::use(self::make($config, $name), $name);
    }

    /**
     * @param $name string|null
     *
     * @return Connection
     */
    public static function connection(?string $name = null): Connection
    {
        $name ??= self::$default;
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        return self::$connections[$name] = self::make(self::config_for($name), $name);
    }

    /**
     * @param $table string
     *
     * @return Query
     */
    public static function table(string $table): Query
    {
        return self::connection()->table($table);
    }

    /**
     * @return Driver
     */
    public static function driver(): Driver
    {
        return self::connection()->driver();
    }

    /**
     * @param $config array<string, mixed>
     * @param $name string
     *
     * @return Connection
     */
    public static function make(array $config, string $name = 'default'): Connection
    {
        $driver = Driver::from_name((string) ($config['driver'] ?? 'sqlite'));
        if (! $driver->is_ready()) {
            throw Driver::not_ready();
        }

        return new Connection(self::pdo($driver, $config), $driver, $name);
    }

    /**
     * @param $name string
     *
     * @return array<string, mixed>
     */
    public static function config_for(string $name): array
    {
        $tree = Config::get('database', []);
        if (! \is_array($tree)) {
            $tree = [];
        }
        $connections = $tree['connections'] ?? null;
        if (\is_array($connections)) {
            $selected = $name === 'default'
                ? (string) ($tree['default'] ?? array_key_first($connections) ?? 'sqlite')
                : $name;
            $row = $connections[$selected] ?? $connections[$name] ?? null;
            if (\is_array($row)) {
                return $row;
            }
        }
        if (isset($tree['driver'])) {
            return $tree;
        }

        return [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ];
    }

    /**
     * @param $driver Driver
     * @param $config array<string, mixed>
     *
     * @return \PDO
     */
    private static function pdo(Driver $driver, array $config): \PDO
    {
        return match ($driver) {
            Driver::Sqlite => self::sqlite($config),
            Driver::Mysql, Driver::Mariadb => self::mysql($config),
            Driver::Pgsql => self::pgsql($config),
            Driver::Clickhouse => throw Driver::not_ready(),
        };
    }

    /**
     * @param $config array<string, mixed>
     *
     * @return \PDO
     */
    private static function sqlite(array $config): \PDO
    {
        $database = (string) ($config['database'] ?? ':memory:');
        if ($database !== ':memory:' && ! \str_starts_with($database, '/')) {
            $database = webapp_path($database);
        }
        if ($database !== ':memory:') {
            $dir = \dirname($database);
            if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
                throw new \RuntimeException('Cannot create sqlite directory '.$dir);
            }
            if (! \is_file($database) && \touch($database) === false) {
                throw new \RuntimeException('Cannot create sqlite database '.$database);
            }
        }

        $pdo = new \PDO('sqlite:'.$database);
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }

    /**
     * @param $config array<string, mixed>
     *
     * @return \PDO
     */
    private static function mysql(array $config): \PDO
    {
        $dsn = \sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            (string) ($config['host'] ?? '127.0.0.1'),
            (string) ($config['port'] ?? '3306'),
            (string) ($config['database'] ?? ''),
            (string) ($config['charset'] ?? 'utf8mb4'),
        );

        return new \PDO(
            $dsn,
            (string) ($config['username'] ?? ''),
            (string) ($config['password'] ?? ''),
        );
    }

    /**
     * @param $config array<string, mixed>
     *
     * @return \PDO
     */
    private static function pgsql(array $config): \PDO
    {
        $dsn = \sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            (string) ($config['host'] ?? '127.0.0.1'),
            (string) ($config['port'] ?? '5432'),
            (string) ($config['database'] ?? ''),
        );

        return new \PDO(
            $dsn,
            (string) ($config['username'] ?? ''),
            (string) ($config['password'] ?? ''),
        );
    }
}
