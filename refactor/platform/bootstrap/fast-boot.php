<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//>
//> Hot path: require config/platform.php (OPcache), read autoload, require it, return.
//> Miss: discover vendor-dir, optional CLI composer install, stamp autoload via ConfigWriter.

$webapp_path = dirname(__DIR__, 2);
$config_path = $webapp_path.'/config/platform.php';
$platform_config = is_file($config_path) ? require $config_path : [];
if (! is_array($platform_config)) {
    $platform_config = [];
}
$autoload_rel = $platform_config['autoload'] ?? 'platform/dependencies/autoload.php';
if (
    is_string($autoload_rel) &&
    $autoload_rel !== '' &&
    ! str_contains($autoload_rel, '..')
) {
    $autoload_abs = $webapp_path.'/'.$autoload_rel;
    if (is_file($autoload_abs)) {
        require $autoload_abs;

        return;
    }
}

/**
 * Miss path: discover vendor dir, stamp config/platform.php, optionally install composer.
 */
(static function (string $webapp_path, string $config_path): void {
    $fail = static function (string $message): never {
        fwrite(STDERR, 'fast-boot: '.$message.PHP_EOL);
        exit(1);
    };

    $load_writer = static function (string $webapp_path): void {
        if (class_exists(\Webkernel\Config\ConfigWriter::class, false)) {
            return;
        }
        foreach ([
            $webapp_path.'/x-webkernel/codebase/src/Config/src/ConfigWriter.php',
            $webapp_path.'/platform/dependencies/webkernel/codebase/src/Config/src/ConfigWriter.php',
        ] as $file) {
            if (is_file($file)) {
                require_once $file;

                return;
            }
        }
    };

    /** Validate and normalise a vendor-dir string to a relative autoload path. */
    $normalize_rel = static function (string $vendor): ?string {
        $vendor = trim(str_replace('\\', '/', $vendor), '/');
        if ($vendor === '' || $vendor[0] === '/') {
            return null;
        }
        foreach (explode('/', $vendor) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        return $vendor.'/autoload.php';
    };

    $candidates = [];

    $composer_json_path = $webapp_path.'/composer.json';
    if (is_file($composer_json_path)) {
        $raw = file_get_contents($composer_json_path);
        if ($raw === false) {
            $fail('unable to read composer.json');
        }
        assert(is_string($raw));
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $fail('composer.json is not valid JSON: '.$e->getMessage());
        }
        if (! is_array($decoded)) {
            $fail('composer.json is not an object.');
        }
        $vendor_dir = $decoded['config']['vendor-dir'] ?? 'vendor';
        if (! is_string($vendor_dir)) {
            $fail('invalid composer config.vendor-dir.');
        }
        $from_json = $normalize_rel($vendor_dir);
        if ($from_json === null) {
            $fail('invalid composer config.vendor-dir.');
        }
        $candidates[$from_json] = true;
    }

    $candidates['platform/dependencies/autoload.php'] = true;
    $candidates['vendor/autoload.php'] = true;
    $candidate_list = array_keys($candidates);

    $find = static function () use ($webapp_path, $candidate_list): ?string {
        foreach ($candidate_list as $rel) {
            if (is_file($webapp_path.'/'.$rel)) {
                return $rel;
            }
        }

        return null;
    };

    $boot = static function (string $rel) use ($webapp_path, $config_path, $load_writer): void {
        $load_writer($webapp_path);
        if (class_exists(\Webkernel\Config\ConfigWriter::class, false)) {
            \Webkernel\Config\ConfigWriter::atomic_rewrite($config_path, ['autoload' => $rel]);
        }
        require $webapp_path.'/'.$rel;
    };

    $rel = $find();
    if ($rel !== null) {
        $boot($rel);

        return;
    }

    if (PHP_SAPI !== 'cli') {
        $fail('vendor autoload missing. Run: composer install');
    }

    fwrite(STDERR, 'fast-boot: installing PHP dependencies…'.PHP_EOL);

    $tmp_phar = null;

    $composer_argv = (static function () use (
        $fail,
        $webapp_path,
        &$tmp_phar
    ): array {
        $path_env = getenv('PATH');
        if (is_string($path_env) && $path_env !== '') {
            foreach (explode(PATH_SEPARATOR, $path_env) as $dir) {
                $bin = rtrim($dir, '/\\').DIRECTORY_SEPARATOR.'composer';
                if (is_file($bin) && is_executable($bin)) {
                    return [$bin];
                }
            }
        }

        if (! ini_get('allow_url_fopen')) {
            $fail('allow_url_fopen is disabled. Run: composer install');
        }

        $http_ctx = stream_context_create([
            'http' => ['timeout' => 120, 'follow_location' => 1],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);

        $phar_bytes = file_get_contents(
            'https://getcomposer.org/download/latest-stable/composer.phar',
            false,
            $http_ctx
        );
        $phar_sha256 = file_get_contents(
            'https://getcomposer.org/download/latest-stable/composer.phar.sha256sum',
            false,
            $http_ctx
        );

        if (
            $phar_bytes === false ||
            $phar_bytes === '' ||
            $phar_sha256 === false ||
            $phar_sha256 === ''
        ) {
            fwrite(STDERR, 'fast-boot: unable to download composer. Run: composer install'.PHP_EOL);
            exit(1);
        }

        $checksum_token = strtok(trim($phar_sha256), " \t");
        if ($checksum_token === false) {
            fwrite(STDERR, 'fast-boot: composer.phar checksum mismatch.'.PHP_EOL);
            exit(1);
        }

        $expected = strtolower($checksum_token);
        $phar_trimmed = ltrim($phar_bytes);

        if (
            preg_match('/^[a-f0-9]{64}$/', $expected) !== 1 ||
            ! hash_equals($expected, hash('sha256', $phar_bytes)) ||
            (! str_starts_with($phar_trimmed, '<?php') &&
                ! str_starts_with($phar_trimmed, '#!/usr/bin/env php'))
        ) {
            fwrite(STDERR, 'fast-boot: composer.phar checksum mismatch.'.PHP_EOL);
            exit(1);
        }

        $tmp_dir = $webapp_path.'/platform/temporary';
        if (! is_dir($tmp_dir) && ! mkdir($tmp_dir, 0775, true) && ! is_dir($tmp_dir)) {
            fwrite(STDERR, 'fast-boot: unable to create platform/temporary.'.PHP_EOL);
            exit(1);
        }

        $tmp_phar = tempnam($tmp_dir, 'wkc');
        if ($tmp_phar === false) {
            fwrite(STDERR, 'fast-boot: unable to write platform/temporary.'.PHP_EOL);
            exit(1);
        }
        if (file_put_contents($tmp_phar, $phar_bytes, LOCK_EX) === false) {
            fwrite(STDERR, 'fast-boot: unable to write temporary composer.phar.'.PHP_EOL);
            exit(1);
        }
        chmod($tmp_phar, 0444);

        return [PHP_BINARY, $tmp_phar];
    })();

    $env = getenv();
    if (! is_array($env)) {
        $env = [];
    }
    unset($env['COMPOSER_HOME']);

    $exit_code = 1;

    try {
        $pipes = [];
        $process = proc_open(
            [
                ...$composer_argv,
                'install',
                '--working-dir='.$webapp_path,
                '--no-interaction',
                '--no-ansi',
                '--no-scripts',
            ],
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes,
            $webapp_path,
            $env,
            ['bypass_shell' => true]
        );

        if ($process === false) {
            fwrite(STDERR, 'fast-boot: unable to start composer. Run: composer install'.PHP_EOL);
            exit(1);
        }

        $status = proc_close($process);
        $exit_code = is_int($status) ? $status : 1;
    } finally {
        if (is_string($tmp_phar) && is_file($tmp_phar)) {
            unlink($tmp_phar);
        }
    }

    $rel = $find();

    if ($exit_code !== 0 || $rel === null) {
        $fail('dependency installation failed. Run: composer install');
    }

    fwrite(STDERR, 'fast-boot: PHP dependencies installed.'.PHP_EOL);
    $boot($rel);
})($webapp_path, $config_path);
