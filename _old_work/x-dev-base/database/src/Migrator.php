<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database;

use Webkernel\PlatformProvider;

/**
 * Runs pending Migration classes from provider MIGRATIONS dirs.
 */
final class Migrator
{
    /** @var array<string, class-string<Migration>> */
    private static array $map = [];

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$map = [];
    }

    /**
     * @param $directories list<string>
     *
     * @return list<string> Ran migration ids
     */
    public function run(array $directories = []): array
    {
        $this->ensure_repository();
        $pending = $this->pending($directories === [] ? $this->directories() : $directories);
        $batch = $this->next_batch();
        $ran = [];
        foreach ($pending as $id => $class) {
            $migration = new $class();
            $migration->up();
            Database::table('migrations')->insert([
                'migration' => $id,
                'batch' => $batch,
            ]);
            $ran[] = $id;
        }

        return $ran;
    }

    /**
     * @param $directories list<string>
     *
     * @return array<string, class-string<Migration>>
     */
    public function pending(array $directories = []): array
    {
        $done = $this->ran();
        $out = [];
        foreach ($this->discover($directories === [] ? $this->directories() : $directories) as $id => $class) {
            if (! isset($done[$id])) {
                $out[$id] = $class;
            }
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    public function directories(): array
    {
        $file = \function_exists('vendor_dir') ? vendor_dir('composer/webkernel_providers.php') : '';
        if ($file === '' || ! \is_file($file)) {
            return [];
        }
        $providers = require $file;
        if (! \is_array($providers)) {
            return [];
        }
        $out = [];
        foreach ($providers as $class) {
            if (! \is_string($class) || $class === '' || ! \is_a($class, PlatformProvider::class, true)) {
                continue;
            }
            foreach ($class::declaration('MIGRATIONS') as $dir) {
                if (\is_string($dir) && $dir !== '' && \is_dir($dir)) {
                    $out[] = \str_replace('\\', '/', $dir);
                }
            }
        }

        return \array_values(\array_unique($out));
    }

    /**
     * @return void
     */
    private function ensure_repository(): void
    {
        if (Schema::has_table('migrations')) {
            return;
        }
        Schema::create('migrations', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
    }

    /**
     * @return array<string, true>
     */
    private function ran(): array
    {
        $out = [];
        foreach (Database::table('migrations')->get() as $row) {
            $id = (string) ($row['migration'] ?? '');
            if ($id !== '') {
                $out[$id] = true;
            }
        }

        return $out;
    }

    /**
     * @return int
     */
    private function next_batch(): int
    {
        $max = 0;
        foreach (Database::table('migrations')->get() as $row) {
            $batch = (int) ($row['batch'] ?? 0);
            if ($batch > $max) {
                $max = $batch;
            }
        }

        return $max + 1;
    }

    /**
     * @param $directories list<string>
     *
     * @return array<string, class-string<Migration>>
     */
    private function discover(array $directories): array
    {
        $out = [];
        foreach ($directories as $dir) {
            $files = \glob(\rtrim($dir, '/').'/*.php');
            if (! \is_array($files)) {
                continue;
            }
            \sort($files, \SORT_STRING);
            foreach ($files as $file) {
                $id = \basename($file, '.php');
                if (isset(self::$map[$id])) {
                    $out[$id] = self::$map[$id];
                    continue;
                }
                $declared = \get_declared_classes();
                $loaded = require $file;
                if ($loaded instanceof Migration) {
                    self::$map[$id] = $loaded::class;
                    $out[$id] = self::$map[$id];
                    continue;
                }
                foreach (\array_diff(\get_declared_classes(), $declared) as $class) {
                    if (\is_a($class, Migration::class, true) && $class !== Migration::class) {
                        self::$map[$id] = $class;
                        $out[$id] = $class;
                    }
                }
            }
        }

        return $out;
    }
}
