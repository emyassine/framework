<?php declare(strict_types=1);

namespace Webkernel\Cache;

use Webkernel\Container\Container;
use Webkernel\Provider\ProviderRegistry;
use Webkernel\Provider\ProviderFingerprint;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates all compilation passes.
 * Compiles routes, config, ACL, views, commands, panels, composables, and classmap.
 */
final class Compiler
{
    private static ?LoggerInterface $logger = null;

    /**
     * Set logger for compilation error reporting.
     */
    public static function set_logger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * Compile all artifacts from all providers.
     * This is the main entry point for compilation.
     */
    public static function compile(Container $container): void
    {
        $providers = self::boot_providers($container);

        $artifacts = [];

        // Pass 1: routes (merged global map + per-fingerprint entries)
        $artifacts['webkernel.global.routes'] = self::compile_routes($providers, $container);

        // Pass 2: config (dot-accessible, merged across providers + env files)
        $artifacts['webkernel.global.config'] = self::compile_config($providers);

        // Pass 3: ACL
        $artifacts['webkernel.global.acl'] = self::compile_acl($providers);

        // Pass 4: views (per-fingerprint namespace => path map)
        $artifacts['webkernel.global.views'] = self::compile_views($providers);

        // Pass 5: commands (flat list of all command classes)
        $artifacts['webkernel.global.commands'] = self::compile_commands($providers);

        // Pass 6: panels
        $artifacts['webkernel.global.panels'] = self::compile_panels($providers);

        // Pass 7: composables
        $artifacts['webkernel.global.composables'] = self::compile_composables($providers);

        // Pass 8: classmap
        $artifacts['webkernel.global.classmap'] = self::compile_classmap($providers);

        CompilationStore::store_all($artifacts);
        CompilationStore::put('webkernel.compiled_at', time());
    }

    /**
     * Boot all providers (register and boot phases).
     * Returns array of instantiated provider objects.
     */
    private static function boot_providers(Container $container): array
    {
        $providers = [];

        foreach (ProviderRegistry::providers() as $class) {
            try {
                $provider = new $class();
                $provider->register($container);
                $providers[] = $provider;
            } catch (\Throwable $e) {
                self::log_error("Provider registration failed for {$class}: " . $e->getMessage());
            }
        }

        foreach ($providers as $provider) {
            try {
                $provider->boot($container);
            } catch (\Throwable $e) {
                self::log_error("Provider boot failed for " . get_class($provider) . ": " . $e->getMessage());
            }
        }

        return $providers;
    }

    /**
     * Log an error during compilation.
     */
    private static function log_error(string $message): void
    {
        if (self::$logger !== null) {
            self::$logger->error($message);
        } else {
            error_log('[Webkernel Compiler] ' . $message);
        }
    }

    // -------------------------------------------------------------------------
    // Resolution helper: checks constant first, falls back to method call.
    // -------------------------------------------------------------------------

    /**
     * Resolve a provider declaration by checking constant first, then method.
     */
    private static function resolve_declaration(object $provider, string $constant, string $method): array
    {
        if ($provider instanceof \Webkernel\Provider\PlatformProvider) {
            try {
                return $provider->declaration($constant, $method);
            } catch (\Throwable $e) {
                self::log_error('Declaration '.$method.' failed for '.get_class($provider).': '.$e->getMessage());

                return [];
            }
        }

        $class = get_class($provider);
        if (defined($class.'::'.$constant)) {
            $value = constant($class.'::'.$constant);

            return is_array($value) ? $value : [];
        }
        if (method_exists($provider, $method)) {
            try {
                $value = $provider->$method();

                return is_array($value) ? $value : [];
            } catch (\Throwable $e) {
                self::log_error('Declaration method '.$method.' failed for '.$class.': '.$e->getMessage());

                return [];
            }
        }

        return [];
    }

    // -------------------------------------------------------------------------
    // Per-artifact compilation passes with isolated error handling
    // -------------------------------------------------------------------------

    /**
     * Compile routes from all providers.
     */
    private static function compile_routes(array $providers, Container $container): array
    {
        $app = \Webkernel\WebApp::get()->boot();
        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'ROUTES', 'routes');
            foreach ($entries as $entry) {
                if (is_string($entry) && is_file($entry)) {
                    $app->declare('routes', [$entry]);
                }
            }
        }
        \Webkernel\Route\Route::app();

        return \Webkernel\Route\Route::list();
    }

    /**
     * Compile configuration from all providers and env files.
     */
    private static function compile_config(array $providers): array
    {
        $config = [];

        $root = function_exists('webapp_path') ? webapp_path() : dirname(__DIR__, 4);
        $base_config = self::load_config_file($root.'/config/app.php');
        $config = array_merge($config, $base_config);

        $env = $_ENV['APP_ENV'] ?? ($_SERVER['APP_ENV'] ?? 'prod');
        $env_config = self::load_config_file($root.'/config/app.'.$env.'.php');
        $config = array_merge($config, $env_config);

        // Merge provider configs (providers override env config)
        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'CONFIG', 'config');
            $config = array_merge($config, $entries);
        }

        return $config;
    }

    /**
     * Load a config file safely.
     */
    private static function load_config_file(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }
        try {
            return require $path;
        } catch (\Throwable $e) {
            self::log_error("Config file load failed for {$path}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compile ACL rules from all providers.
     */
    private static function compile_acl(array $providers): array
    {
        $acl = [];

        foreach ($providers as $provider) {
            try {
                if (method_exists($provider, 'acl')) {
                    $acl = array_merge_recursive($acl, $provider->acl());
                }
            } catch (\Throwable $e) {
                self::log_error("ACL compilation failed for " . get_class($provider) . ": " . $e->getMessage());
            }
        }

        return $acl;
    }

    /**
     * Compile views from all providers.
     */
    private static function compile_views(array $providers): array
    {
        $map = [];

        foreach ($providers as $provider) {
            try {
                $entries = self::resolve_declaration($provider, 'VIEWS', 'views');
                $fingerprint = ProviderFingerprint::for(get_class($provider));

                foreach ($entries as $entry) {
                    $map[$fingerprint][] = $entry;
                }
            } catch (\Throwable $e) {
                self::log_error("Views compilation failed for " . get_class($provider) . ": " . $e->getMessage());
            }
        }

        return $map;
    }

    /**
     * Compile commands from all providers.
     */
    private static function compile_commands(array $providers): array
    {
        $commands = [];

        foreach ($providers as $provider) {
            try {
                $entries = self::resolve_declaration($provider, 'COMMANDS', 'commands');

                foreach ($entries as $entry) {
                    if (is_dir($entry)) {
                        array_push($commands, ...self::scan_command_dir($entry));
                    } else {
                        $commands[] = $entry;
                    }
                }
            } catch (\Throwable $e) {
                self::log_error("Commands compilation failed for " . get_class($provider) . ": " . $e->getMessage());
            }
        }

        return array_values(array_filter($commands, static function ($command): bool {
            return is_string($command) && class_exists($command);
        }));
    }

    /**
     * @return list<class-string>
     */
    private static function scan_command_dir(string $dir): array
    {
        return [];
    }

    /**
     * Compile panels from all providers.
     */
    private static function compile_panels(array $providers): array
    {
        $panels = [];

        foreach ($providers as $provider) {
            try {
                $entries = self::resolve_declaration($provider, 'PANELS', 'panels');
                array_push($panels, ...$entries);
            } catch (\Throwable $e) {
                self::log_error("Panels compilation failed for " . get_class($provider) . ": " . $e->getMessage());
            }
        }

        return $panels;
    }

    /**
     * Compile composables from all providers.
     */
    private static function compile_composables(array $providers): array
    {
        $composables = [];

        foreach ($providers as $provider) {
            try {
                $entries = self::resolve_declaration($provider, 'COMPOSABLES', 'composables');
                array_push($composables, ...$entries);
            } catch (\Throwable $e) {
                self::log_error("Composables compilation failed for " . get_class($provider) . ": " . $e->getMessage());
            }
        }

        return $composables;
    }

    /**
     * Compile classmap from all providers.
     */
    private static function compile_classmap(array $providers): array
    {
        $map = [];

        foreach ($providers as $provider) {
            try {
                $entries = self::resolve_declaration($provider, 'CLASSMAP', 'classmap');
                $map = array_merge($map, $entries);
            } catch (\Throwable $e) {
                self::log_error("Classmap compilation failed for " . get_class($provider) . ": " . $e->getMessage());
            }
        }

        return $map;
    }
}
