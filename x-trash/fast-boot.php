<?php declare(strict_types=1);

use Composer\Console\Application as ComposerApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Webkernel\Lifecycle\Vite\ViteWebapp;

/**
 * Host fast-boot: critical environment checks and self-heal.
 *
 * - PHP Composer autoload (install if missing, CLI only)
 * - platform integrity (fast-boot, bootstrap-app, x-test) when codebase is loaded
 * - local npm deps (install if node_modules missing, CLI only)
 * - vite.webapp.ts via ViteWebapp (regenerate if missing or --ensure-vite-webapp)
 *
 * Used by:
 *   bootstrap/app.php          (require)
 *   vite.config.ts             php bootstrap/fast-boot.php --ensure-vite-webapp
 *   CLI                        php bootstrap/fast-boot.php
 */

(new class {
    private const string COMPOSER_JSON = 'composer.json';

    private const string PACKAGE_JSON = 'package.json';

    private const string DEFAULT_VENDOR = 'vendor';

    private const string AUTOLOAD_FILE = 'autoload.php';

    private const string COMPOSER_HOME_DIR = 'bootstrap/cache/composer-home';

    private const string COMPOSER_BIN_DIR = 'bin';

    private const string COMPOSER_PHAR_FILE = 'composer.phar';

    private const string COMPOSER_PHAR_URL = 'https://getcomposer.org/download/latest-stable/composer.phar';

    private const string VITE_WEBAPP = 'vite.webapp.ts';

    private const string NODE_MODULES = 'node_modules';

    private const string ERR_COMPOSER_JSON = 'composer.json not found.';

    private const string ERR_VENDOR_MISSING = 'Vendor autoload missing. Run: composer install';

    private const string ERR_CHDIR_DISABLED = 'Vendor autoload missing. PHP chdir is disabled. Run: composer install';

    private const string ERR_COMPOSER_UNAVAILABLE = 'Composer unavailable. Run: composer install';

    private const string ERR_INSTALL_FAILED = 'Dependency installation failed. Run: composer install';

    private const string ERR_NPM_FAILED = 'npm install failed. Run: npm install';

    private const string ERR_VITE_WEBAPP = 'Failed to generate vite.webapp.ts.';

    private const string ERR_INTEGRITY = 'fast-boot: integrity failed';

    /** Platform rules run after Composer autoload is available. */
    private const array INTEGRITY_SLUGS = [
        'fast-boot',
        'bootstrap-app',
        'x-test',
    ];

    private const string LOG_INSTALLING_PHP = 'fast-boot: installing PHP dependencies…';

    private const string LOG_INSTALLED_PHP = 'fast-boot: PHP dependencies installed.';

    private const string LOG_INSTALLING_NPM = 'fast-boot: installing npm dependencies…';

    private const string LOG_INSTALLED_NPM = 'fast-boot: npm dependencies installed.';

    private const string LOG_VITE_WEBAPP = 'fast-boot: ensuring vite.webapp.ts…';

    /** @var list<string> */
    private array $argv;

    public function __construct()
    {
        $this->argv = $_SERVER['argv'] ?? [];
    }

    public function fast_boot(): void
    {
        $root = $this->root();
        $autoload = $this->resolve_autoload($root);

        if (! $this->try_require($autoload)) {
            $this->assert_install_allowed();
            $this->install_php($root, $autoload);
            // install_php exits(0) after success so the process restarts cleanly.
        }

        // Needs autoload (webkernel_integrity). Heals platform-owned host files.
        $this->ensure_integrity();

        if (PHP_SAPI === 'cli') {
            $this->ensure_npm($root);
        }

        $force_vite = $this->has_flag('--ensure-vite-webapp')
            || $this->has_flag('--force-vite-webapp');

        $this->ensure_vite_webapp($root, $force_vite);
    }

    /**
     * Run platform integrity rules (no-op if codebase helpers are not loaded).
     * match_or_replace rules self-heal; hard fail only when a rule still fails.
     */
    private function ensure_integrity(): void
    {
        if (! function_exists('webkernel_integrity')) {
            return;
        }

        foreach (self::INTEGRITY_SLUGS as $slug) {
            $result = webkernel_integrity($slug);
            if ($result->failed()) {
                $detail = $result->message !== '' ? $result->message : 'unknown error';
                $this->fail(self::ERR_INTEGRITY . " [{$slug}]: {$detail}");
            }
        }
    }

    private function root(): string
    {
        return dirname(__DIR__);
    }

    private function bootstrap_dir(): string
    {
        return __DIR__;
    }

    private function has_flag(string $flag): bool
    {
        return in_array($flag, $this->argv, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function read_composer_config(string $root): array
    {
        $path = $root . '/' . self::COMPOSER_JSON;

        if (! is_file($path)) {
            $this->fail(self::ERR_COMPOSER_JSON);
        }

        return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function resolve_autoload(string $root): string
    {
        $composer = $this->read_composer_config($root);
        $vendor = (string) (($composer['config']['vendor-dir'] ?? null) ?: self::DEFAULT_VENDOR);

        return $root . '/' . $vendor . '/' . self::AUTOLOAD_FILE;
    }

    private function try_require(string $autoload): bool
    {
        if (! is_file($autoload)) {
            return false;
        }

        require $autoload;

        return true;
    }

    private function assert_install_allowed(): void
    {
        if (PHP_SAPI !== 'cli') {
            $this->fail(self::ERR_VENDOR_MISSING);
        }

        if ($this->php_function_disabled('chdir')) {
            $this->fail(self::ERR_CHDIR_DISABLED);
        }
    }

    private function php_function_disabled(string $function): bool
    {
        if (! function_exists($function)) {
            return true;
        }

        $disabled = array_map(trim(...), explode(',', strtolower((string) ini_get('disable_functions'))));

        return in_array($function, $disabled, true);
    }

    private function resolve_composer_phar(): string
    {
        $phar = $this->bootstrap_dir() . '/' . self::COMPOSER_BIN_DIR . '/' . self::COMPOSER_PHAR_FILE;

        if (is_file($phar)) {
            return $phar;
        }

        $bin_dir = $this->bootstrap_dir() . '/' . self::COMPOSER_BIN_DIR;

        if (! is_dir($bin_dir)) {
            mkdir($bin_dir, 0755, true);
        }

        $context = stream_context_create(['http' => ['timeout' => 120, 'follow_location' => 1]]);
        $bytes = @file_get_contents(self::COMPOSER_PHAR_URL, false, $context);

        if ($bytes === false || $bytes === '') {
            $this->fail(self::ERR_COMPOSER_UNAVAILABLE);
        }

        file_put_contents($phar, $bytes, LOCK_EX);

        return $phar;
    }

    private function install_php(string $root, string $autoload): void
    {
        fwrite(STDERR, self::LOG_INSTALLING_PHP . PHP_EOL);

        putenv('COMPOSER_HOME=' . $root . '/' . self::COMPOSER_HOME_DIR);

        require 'phar://' . $this->resolve_composer_phar() . '/vendor/autoload.php';

        $application = new ComposerApplication();
        $application->setAutoExit(false);

        $exit_code = $application->run(
            new ArrayInput([
                'command' => 'install',
                '--working-dir' => $root,
                '--no-interaction' => true,
                '--no-ansi' => true,
                '--no-scripts' => true,
            ]),
            new ConsoleOutput(),
        );

        if ($exit_code !== 0 || ! is_file($autoload)) {
            $this->fail(self::ERR_INSTALL_FAILED);
        }

        fwrite(STDERR, self::LOG_INSTALLED_PHP . PHP_EOL);
        exit(0);
    }

    private function ensure_npm(string $root): void
    {
        if (! is_file($root . '/' . self::PACKAGE_JSON)) {
            return;
        }

        if (is_dir($root . '/' . self::NODE_MODULES)) {
            return;
        }

        $npm = $this->resolve_npm_binary();
        fwrite(STDERR, self::LOG_INSTALLING_NPM . PHP_EOL);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            [$npm, 'install'],
            $descriptors,
            $pipes,
            $root,
            null,
            ['bypass_shell' => true],
        );

        if (! is_resource($process)) {
            $this->fail(self::ERR_NPM_FAILED);
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($stdout !== '') {
            fwrite(STDERR, rtrim($stdout) . PHP_EOL);
        }
        if ($stderr !== '') {
            fwrite(STDERR, rtrim($stderr) . PHP_EOL);
        }

        if ($exit !== 0 || ! is_dir($root . '/' . self::NODE_MODULES)) {
            $this->fail(self::ERR_NPM_FAILED);
        }

        fwrite(STDERR, self::LOG_INSTALLED_NPM . PHP_EOL);
    }

    private function resolve_npm_binary(): string
    {
        $which = trim((string) shell_exec('command -v npm 2>/dev/null'));
        if ($which !== '' && is_executable($which)) {
            return $which;
        }

        $home = getenv('HOME') ?: (getenv('USERPROFILE') ?: '');
        if ($home !== '') {
            $nvm_root = $home . '/.nvm/versions/node';
            if (is_dir($nvm_root)) {
                $versions = glob($nvm_root . '/*/bin/npm') ?: [];
                rsort($versions);
                foreach ($versions as $candidate) {
                    if (is_executable($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        return 'npm';
    }

    /**
     * Ensure vite.webapp.ts via ViteWebapp when missing or forced.
     */
    private function ensure_vite_webapp(string $root, bool $force): void
    {
        $snapshot = $root . '/' . self::VITE_WEBAPP;

        if (! $force && is_file($snapshot)) {
            return;
        }

        if (! class_exists(ViteWebapp::class)) {
            if ($force) {
                $this->fail(self::ERR_VITE_WEBAPP . ' (ViteWebapp class not available).');
            }

            return;
        }

        fwrite(STDERR, self::LOG_VITE_WEBAPP . PHP_EOL);

        // Use $root from fast-boot itself (not webapp_path()) — IDE-friendly.
        $result = (new ViteWebapp($root))->generate(strict: false);

        if (! $result->ok || ! is_file($root . '/' . self::VITE_WEBAPP)) {
            $detail = $result->stderr !== '' ? $result->stderr : $result->raw;
            $this->fail(self::ERR_VITE_WEBAPP . ($detail !== '' ? ' ' . $detail : ''));
        }
    }

    private function fail(string $message): never
    {
        throw new RuntimeException($message);
    }
})->fast_boot();
