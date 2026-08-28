<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//>
//> Hot path tier-1 : hardcoded autoload -> require -> done. Zero class overhead.
//> Hot path tier-2 : config/platform.php stamped path (OPcache) -> require -> done.
//> Miss path       : discover vendor-dir, stamp config, optional CLI composer install.

// ---------------------------------------------------------------------------
// Tier-1 bypass - fastest possible path, no class, no config read.
// OPcache compiles this to a single is_file + require with no allocations.
// ---------------------------------------------------------------------------

$autoload = __DIR__ . '/dependencies/packagist/autoload.php';
if (is_file($autoload)) { require $autoload; return; }

// --- Tier-2 + miss path - only reached when packagist/autoload.php is absent-

use Webkernel\Config\ConfigWriter;

(new class {

	// Constants -------------------------------------------------------------

    private const DEPS_SUBDIR         = 'platform/dependencies';
    private const AUTOLOAD_DEFAULT    = self::DEPS_SUBDIR . '/autoload.php';
    private const CONFIG_REL          = 'config/platform.php';
    private const WRITER_REL          = self::DEPS_SUBDIR . '/packagist/webkernel/codebase/src/Config/ConfigWriter.php';
    private const TMP_DIR_REL         = 'platform/temporary';
    private const COMPOSER_PHAR_URL   = 'https://getcomposer.org/download/latest-stable/composer.phar';
    private const COMPOSER_SHA256_URL = 'https://getcomposer.org/download/latest-stable/composer.phar.sha256sum';
    private const COMPOSER_TIMEOUT    = 120;
    private const FALLBACK_CANDIDATES = [ self::AUTOLOAD_DEFAULT, 'vendor/autoload.php' ];

    // State -----------------------------------------------------------------

    private string $webapp_path;
    private string $config_path;

    // Entry point (fluent so callers can chain if needed) -------------------

    public function fast_boot(string $webapp_path): static
    {
        $this->webapp_path = $webapp_path;
        $this->config_path = $webapp_path . '/' . self::CONFIG_REL;

        // Tier-2: stamped path from config (OPcache-warm on subsequent requests).
        $platform_config = is_file($this->config_path) ? require $this->config_path : [];
        if (!is_array($platform_config)) {
            $platform_config = [];
        }

        $autoload_rel = $platform_config['autoload'] ?? self::AUTOLOAD_DEFAULT;

        if ($this->require_if_valid($autoload_rel)) {
            return $this;
        }

        // Miss path.
        $this->handle_miss();

        return $this;
    }

    // -----------------------------------------------------------------------
    // Tier-2 require helper
    // -----------------------------------------------------------------------

    private function require_if_valid(mixed $rel): bool
    {
        if (!is_string($rel) || $rel === '' || str_contains($rel, '..')) {
            return false;
        }

        $abs = $this->webapp_path . '/' . $rel;

        if (!is_file($abs)) {
            return false;
        }

        require $abs;
        return true;
    }

    // -----------------------------------------------------------------------
    // Miss path
    // -----------------------------------------------------------------------

    private function handle_miss(): void
    {
        $candidates = $this->build_candidate_list();
        $rel        = $this->find_first_existing($candidates);

        if ($rel !== null) {
            $this->stamp_autoload_path($rel);
            require $this->webapp_path . '/' . $rel;
            return;
        }

        if (PHP_SAPI !== 'cli') {
            $this->fail_with_message('vendor autoload missing. Run: composer install');
        }

        $this->run_composer_install();

        $rel = $this->find_first_existing($candidates);

        if ($rel === null) {
            $this->fail_with_message('dependency installation failed. Run: composer install');
        }

        fwrite(STDERR, 'fast-boot: PHP dependencies installed.' . PHP_EOL);

        $this->stamp_autoload_path($rel);
        require $this->webapp_path . '/' . $rel;
    }

    // -----------------------------------------------------------------------
    // Candidate discovery
    // -----------------------------------------------------------------------

    private function build_candidate_list(): array
    {
        $candidates = [];

        $from_json = $this->read_vendor_dir_from_composer();
        if ($from_json !== null) {
            $candidates[$from_json] = true;
        }

        foreach (self::FALLBACK_CANDIDATES as $c) {
            $candidates[$c] = true;
        }

        return array_keys($candidates);
    }

    private function read_vendor_dir_from_composer(): ?string
    {
        $composer_json = $this->webapp_path . '/composer.json';

        if (!is_file($composer_json)) {
            return null;
        }

        $raw = file_get_contents($composer_json);
        if ($raw === false) {
            $this->fail_with_message('unable to read composer.json');
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->fail_with_message('composer.json is not valid JSON: ' . $e->getMessage());
        }

        if (!is_array($decoded)) {
            $this->fail_with_message('composer.json is not an object.');
        }

        $vendor_dir = $decoded['config']['vendor-dir'] ?? 'vendor';

        if (!is_string($vendor_dir)) {
            $this->fail_with_message('invalid composer config.vendor-dir.');
        }

        return $this->normalize_vendor_dir($vendor_dir);
    }

    private function normalize_vendor_dir(string $vendor): ?string
    {
        $vendor = trim(str_replace('\\', '/', $vendor), '/');

        if ($vendor === '' || $vendor[0] === '/') {
            return null;
        }

        foreach (explode('/', $vendor) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        return $vendor . '/autoload.php';
    }

    private function find_first_existing(array $candidates): ?string
    {
        foreach ($candidates as $rel) {
            if (is_file($this->webapp_path . '/' . $rel)) {
                return $rel;
            }
        }
        return null;
    }

    // -----------------------------------------------------------------------
    // Config stamping (so next request hits tier-2 at worst)
    // -----------------------------------------------------------------------

    private function stamp_autoload_path(string $rel): void
    {
        $this->ensure_config_writer();

        if (class_exists(ConfigWriter::class, false)) {
            ConfigWriter::atomic_rewrite($this->config_path, ['autoload' => $rel]);
        }
    }

    private function ensure_config_writer(): void
    {
        if (class_exists(ConfigWriter::class, false)) {
            return;
        }

        $file = $this->webapp_path . '/' . self::WRITER_REL;
        if (is_file($file)) {
            require_once $file;
        }
    }

    // -----------------------------------------------------------------------
    // Composer bootstrap (CLI miss path only)
    // -----------------------------------------------------------------------

    private function run_composer_install(): void
    {
        fwrite(STDERR, 'fast-boot: installing PHP dependencies...' . PHP_EOL);

        $tmp_phar      = null;
        $composer_argv = $this->resolve_composer_argv($tmp_phar);
        $exit_code     = 1;

        try {
            $pipes   = [];
            $process = proc_open(
                [
                    ...$composer_argv,
                    'install',
                    '--working-dir=' . $this->webapp_path,
                    '--no-interaction',
                    '--no-ansi',
                    '--no-scripts',
                ],
                [0 => STDIN, 1 => STDOUT, 2 => STDERR],
                $pipes,
                $this->webapp_path,
                $this->clean_env(),
                ['bypass_shell' => true]
            );

            if ($process === false) {
                $this->fail_with_message('unable to start composer. Run: composer install');
            }

            $status    = proc_close($process);
            $exit_code = is_int($status) ? $status : 1;
        } finally {
            if (is_string($tmp_phar) && is_file($tmp_phar)) {
                unlink($tmp_phar);
            }
        }

        if ($exit_code !== 0) {
            $this->fail_with_message('composer exited with code ' . $exit_code . '. Run: composer install');
        }
    }

    private function resolve_composer_argv(?string &$tmp_phar): array
    {
        $bin = $this->find_composer_on_path();
        if ($bin !== null) {
            return [$bin];
        }

        if (!ini_get('allow_url_fopen')) {
            $this->fail_with_message('allow_url_fopen is disabled. Run: composer install');
        }

        $phar_bytes = $this->download_composer_phar();
        $tmp_phar   = $this->write_tmp_phar($phar_bytes);

        return [PHP_BINARY, $tmp_phar];
    }

    private function find_composer_on_path(): ?string
    {
        $path_env = getenv('PATH');
        if (!is_string($path_env) || $path_env === '') {
            return null;
        }

        foreach (explode(PATH_SEPARATOR, $path_env) as $dir) {
            $bin = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'composer';
            if (is_file($bin) && is_executable($bin)) {
                return $bin;
            }
        }

        return null;
    }

    private function download_composer_phar(): string
    {
        $ctx = stream_context_create([
            'http' => ['timeout' => self::COMPOSER_TIMEOUT, 'follow_location' => 1],
            'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);

        $phar_bytes = file_get_contents(self::COMPOSER_PHAR_URL, false, $ctx);
        $sha256_sum = file_get_contents(self::COMPOSER_SHA256_URL, false, $ctx);

        if ($phar_bytes === false || $phar_bytes === '' || $sha256_sum === false || $sha256_sum === '') {
            $this->fail_with_message('unable to download composer. Run: composer install');
        }

        $checksum_token = strtok(trim($sha256_sum), " \t");
        if ($checksum_token === false) {
            $this->fail_with_message('composer.phar checksum line is empty.');
        }

        $expected    = strtolower($checksum_token);
        $phar_trimmed = ltrim($phar_bytes);

        if (
            preg_match('/^[a-f0-9]{64}$/', $expected) !== 1 ||
            !hash_equals($expected, hash('sha256', $phar_bytes)) ||
            (!str_starts_with($phar_trimmed, '<?php') && !str_starts_with($phar_trimmed, '#!/usr/bin/env php'))
        ) {
            $this->fail_with_message('composer.phar checksum mismatch.');
        }

        return $phar_bytes;
    }

    private function write_tmp_phar(string $phar_bytes): string
    {
        $tmp_dir = $this->webapp_path . '/' . self::TMP_DIR_REL;

        if (!is_dir($tmp_dir) && !mkdir($tmp_dir, 0775, true) && !is_dir($tmp_dir)) {
            $this->fail_with_message('unable to create ' . self::TMP_DIR_REL . '.');
        }

        $tmp = tempnam($tmp_dir, 'wkc');
        if ($tmp === false) {
            $this->fail_with_message('unable to create temp file in ' . self::TMP_DIR_REL . '.');
        }

        if (file_put_contents($tmp, $phar_bytes, LOCK_EX) === false) {
            $this->fail_with_message('unable to write temporary composer.phar.');
        }

        chmod($tmp, 0444);

        return $tmp;
    }

    private function clean_env(): array
    {
        $env = getenv();
        if (!is_array($env)) {
            $env = [];
        }
        unset($env['COMPOSER_HOME']);
        return $env;
    }

    // Error helper ----------------------------------------------------------

    /** @return never */
    private function fail_with_message(string $message): never
    {
        fwrite(STDERR, 'fast-boot: ' . $message . PHP_EOL); exit(1);
    }

})->fast_boot(webapp_path: dirname(__DIR__, 1));
