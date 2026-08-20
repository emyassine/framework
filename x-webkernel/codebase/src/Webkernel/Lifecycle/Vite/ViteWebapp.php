<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite;

use Composer\IO\IOInterface;
use RuntimeException;
use Webkernel\Lifecycle\Vite\Data\Result;
use Webkernel\Lifecycle\Vite\Generator\WebappGenerator;

# Lifecycle API: generate/ensure root vite.webapp.ts and run npm asset builds.
#
# Snapshot generation is pure PHP (no Node required). npm/vite still run under Node.
# npm workspaces (host package.json) use Webkernel-only globs
# (vendor/webkernel, modules, software). Runtime walks webapp.modules (Composer).
# Packages need a package.json to appear as npm workspaces.
# Composer post-autoload-dump only calls this class; all JS logic lives here.
final class ViteWebapp
{
    /** @see WebappGenerator::CLI_PREFIX */
    public const CLI_PREFIX = WebappGenerator::CLI_PREFIX;

    private const NPM_BUILD_ATTEMPTS = 3;

    public function __construct(
        private readonly string $project_root,
    ) {
    }

    /**
     * Rewrite vite.webapp.ts from Composer-installed Webkernel packages only
     * (entries, aliases, Tailwind @source globs). Not all of vendor/.
     */
    public function generate(bool $strict = false, ?IOInterface $io = null): Result
    {
        return WebappGenerator::generate($this->project_root, $io, $strict);
    }

    public function snapshot_path(): string
    {
        return WebappGenerator::snapshot_path($this->project_root);
    }

    /**
     * Ensure the snapshot exists. Does not overwrite if already present
     * (use generate() / vite_npm_build() / composer dump-autoload to refresh).
     */
    public function ensure(?IOInterface $io = null): void
    {
        if (is_file($this->snapshot_path())) {
            return;
        }

        $result = $this->generate(strict: false, io: $io);
        if (! $result->ok) {
            throw new RuntimeException(
                'webkernel: failed to generate vite.webapp.ts: '
                . ($result->stderr !== '' ? $result->stderr : $result->raw),
            );
        }
    }

    /**
     * Always regenerate vite.webapp.ts then run the host npm build script.
     * Retries on failure (regenerate between attempts).
     *
     * @param  string|null  $npm_binary  null = auto-detect (PATH / nvm)
     */
    public function vite_npm_build(
        ?string $npm_binary = null,
        string $script = 'build',
        ?IOInterface $io = null,
    ): Result {
        $npm_binary ??= self::resolve_npm_binary();

        $this->cli_blank($io);
        $this->cli_echo('JavaScript assets — snapshot + npm run build…', $io);
        $this->cli_blank($io);

        $last_result = Result::failure('webkernel: vite_npm_build did not run');

        for ($attempt = 1; $attempt <= self::NPM_BUILD_ATTEMPTS; $attempt++) {
            $this->cli_blank($io);
            $this->cli_echo(sprintf('-- snapshot  (attempt %d/%d) --', $attempt, self::NPM_BUILD_ATTEMPTS), $io);
            $this->cli_blank($io);

            $generated = $this->generate(strict: false, io: $io);
            if (! $generated->ok) {
                $last_result = Result::failure(
                    stderr: $generated->stderr !== ''
                        ? $generated->stderr
                        : 'vite.webapp.ts generation failed before npm build',
                    exit_code: $generated->exit_code !== 0 ? $generated->exit_code : 1,
                );
                $this->cli_echo('snapshot failed: ' . $last_result->stderr, $io, is_error: true);
                $this->cli_blank($io);
                continue;
            }

            $this->cli_echo(sprintf('-- npm run %s --', $script), $io);
            $this->cli_blank($io);

            $last_result = $this->run_npm($npm_binary, $script, $io);
            if ($last_result->ok) {
                $this->cli_blank($io);
                $this->cli_echo(sprintf('npm run %s ok (attempt %d)', $script, $attempt), $io);
                $this->cli_blank($io);

                // Filament package CSS/JS land under public/css|js/{package}/ via
                // filament:assets. npm package.json already chains this after vite;
                // call again here for composer/lifecycle paths and idempotent sync.
                $published = $this->publish_filament_assets($io);
                if (! $published->ok) {
                    $this->cli_echo(
                        'filament:assets failed (Vite build ok): '.$published->stderr,
                        $io,
                        is_error: true,
                    );
                    // Non-fatal for the Vite build itself — host may not boot yet.
                }

                $this->cli_echo('JavaScript assets built ok', $io);
                $this->cli_blank($io);

                return $last_result;
            }

            $this->cli_blank($io);
            $this->cli_echo(sprintf(
                'npm run %s failed (exit %d)%s',
                $script,
                $last_result->exit_code,
                $attempt < self::NPM_BUILD_ATTEMPTS ? ' -- retrying after regenerate...' : ' -- giving up',
            ), $io, is_error: true);

            if ($last_result->stderr !== '') {
                $this->cli_echo($last_result->stderr, $io, is_error: true);
            }

            $this->cli_blank($io);
        }

        $detail = $last_result->stderr !== '' ? $last_result->stderr : $last_result->raw;
        $this->cli_echo(sprintf(
            'build failed (exit %d)%s',
            $last_result->exit_code,
            $detail !== '' ? ': ' . self::shorten($detail) : '',
        ), $io, is_error: true);
        $this->cli_blank($io);

        return $last_result;
    }

    /** Prefer PATH npm; fall back to common nvm locations when Composer strips env. */
    public static function resolve_npm_binary(): string
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
     * Publish Filament package assets to public/ (css/js/fonts).
     *
     * Prefer Artisan::call when the app is bootstrapped; otherwise shell
     * `php artisan filament:assets` from the project root.
     */
    public function publish_filament_assets(?IOInterface $io = null): Result
    {
        $this->cli_echo('Filament assets — php artisan filament:assets…', $io);

        $artisan = $this->project_root.'/artisan';
        if (! is_file($artisan)) {
            $this->cli_echo('skip filament:assets (no artisan in project root)', $io);

            return Result::success(path: '', raw: 'skipped: no artisan');
        }

        // Bootstrapped Laravel (e.g. artisan command / tests): use Artisan facade.
        if (
            class_exists(\Illuminate\Support\Facades\Artisan::class)
            && function_exists('app')
            && app()->bound(\Illuminate\Contracts\Console\Kernel::class)
        ) {
            try {
                $exit = \Illuminate\Support\Facades\Artisan::call('filament:assets', [
                    '--no-interaction' => true,
                ]);
                $output = trim(\Illuminate\Support\Facades\Artisan::output());
                if ($output !== '') {
                    $this->cli_raw(rtrim($output).PHP_EOL, $io);
                    $this->cli_blank($io);
                }
                if ($exit === 0) {
                    $this->cli_echo('filament:assets ok (Artisan::call)', $io);

                    return Result::success(path: '', raw: $output);
                }

                return Result::failure(
                    stderr: $output !== '' ? $output : 'filament:assets exit '.$exit,
                    exit_code: $exit,
                );
            } catch (\Throwable $e) {
                $this->cli_echo(
                    'Artisan::call failed, falling back to CLI: '.$e->getMessage(),
                    $io,
                    is_error: true,
                );
            }
        }

        return $this->run_php_artisan(['filament:assets', '--no-interaction'], $io);
    }

    /**
     * @param  list<string>  $args  after "artisan"
     */
    private function run_php_artisan(array $args, ?IOInterface $io = null): Result
    {
        $command = [PHP_BINARY, $this->project_root.'/artisan', ...$args];
        $this->cli_echo('running: '.implode(' ', $command), $io);
        $this->cli_blank($io);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            $command,
            $descriptors,
            $pipes,
            $this->project_root,
            null,
            ['bypass_shell' => true],
        );

        if (! is_resource($process)) {
            return Result::failure(stderr: 'failed to start: '.implode(' ', $command), exit_code: -1);
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit_code = proc_close($process);

        if ($stdout !== '') {
            $this->cli_raw(rtrim($stdout).PHP_EOL, $io);
            $this->cli_blank($io);
        }

        $ok = $exit_code === 0;
        if ($ok) {
            $this->cli_echo('filament:assets ok (CLI)', $io);
        }

        return $ok
            ? Result::success(path: '', raw: trim($stdout))
            : Result::failure(
                stderr: trim($stderr) !== '' ? trim($stderr) : trim($stdout),
                exit_code: is_int($exit_code) ? $exit_code : -1,
            );
    }

    private function run_npm(string $npm_binary, string $script, ?IOInterface $io = null): Result
    {
        $command = [$npm_binary, 'run', $script];
        $this->cli_echo('running: ' . implode(' ', $command), $io);
        $this->cli_blank($io);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            $command,
            $descriptors,
            $pipes,
            $this->project_root,
            null,
            ['bypass_shell' => true],
        );

        if (! is_resource($process)) {
            throw new RuntimeException('webkernel: failed to start: ' . implode(' ', $command));
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit_code = proc_close($process);

        if ($stdout !== '') {
            $this->cli_raw(rtrim($stdout) . PHP_EOL, $io);
            $this->cli_blank($io);
        }

        $ok = $exit_code === 0;
        $trimmed_stdout = trim($stdout);
        $trimmed_stderr = trim($stderr);
        $path = is_file($this->snapshot_path()) ? 'vite.webapp.ts' : null;

        return $ok
            ? Result::success(path: $path ?? '', raw: $trimmed_stdout)
            : Result::failure(stderr: $trimmed_stderr, exit_code: is_int($exit_code) ? $exit_code : -1);
    }

    private static function shorten(string $text, int $max = 400): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        return strlen($text) > $max ? substr($text, 0, $max) . '…' : $text;
    }

    private function cli_blank(?IOInterface $io = null): void
    {
        if ($io !== null) {
            $io->write('');
            return;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDOUT, PHP_EOL);
        }
    }

    /** Unprefixed block (npm/vite stdout). */
    private function cli_raw(string $text, ?IOInterface $io = null): void
    {
        if ($io !== null) {
            $io->write($text);
            return;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDOUT, $text);
        }
    }

    private function cli_echo(string $message, ?IOInterface $io = null, bool $is_error = false): void
    {
        $prefix = self::CLI_PREFIX;

        if ($io !== null) {
            if ($is_error) {
                $io->writeError('<comment>' . $prefix . '</comment> ' . $message);
            } else {
                $io->write('<info>' . $prefix . '</info> ' . $message);
            }
            return;
        }

        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            return;
        }

        fwrite($is_error ? STDERR : STDOUT, $prefix . ' ' . $message . PHP_EOL);
    }
}
