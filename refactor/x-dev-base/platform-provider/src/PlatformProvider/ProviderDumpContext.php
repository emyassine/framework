<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\PlatformProvider;

use Composer\Package\PackageInterface;
use Composer\Script\Event;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webkernel\PlatformProvider;

final readonly class ProviderDumpContext
{
    /**
     * @param list<class-string<PlatformProvider>> $providers
     * @param array<class-string, string> $classmap
     */
    private function __construct(
        public string $composer_dir,
        public array $providers,
        public array $classmap,
    ) {}

    public static function from_event(Event $event): self
    {
        $vendor_dir = rtrim((string) $event->getComposer()->getConfig()->get('vendor-dir'), '/\\');
        $classmap = self::classmap($event);
        $providers = [];

        foreach (self::packages($event) as $package) {
            $provider = $package->getExtra()['webkernel']['provider'] ?? null;
            if (! is_string($provider) || $provider === '') {
                continue;
            }
            self::ensure_class($provider, $classmap);
            if (is_a($provider, PlatformProvider::class, true) && ! in_array($provider, $providers, true)) {
                $providers[] = $provider;
            }
        }

        sort($providers, SORT_STRING);

        return new self($vendor_dir.'/composer', $providers, $classmap);
    }

    /**
     * @return list<PackageInterface>
     */
    private static function packages(Event $event): array
    {
        return [
            $event->getComposer()->getPackage(),
            ...$event->getComposer()->getRepositoryManager()->getLocalRepository()->getPackages(),
        ];
    }

    /**
     * @return array<class-string, string>
     */
    private static function classmap(Event $event): array
    {
        $map = [];
        foreach (self::packages($event) as $package) {
            $install_path = $package->getInstallationSource() === 'root'
                ? $package->getTargetDir()
                : $event->getComposer()->getInstallationManager()->getInstallPath($package);
            if (! is_string($install_path) || $install_path === '') {
                continue;
            }
            foreach (($package->getAutoload()['psr-4'] ?? []) as $namespace => $dirs) {
                foreach ((array) $dirs as $dir) {
                    self::scan_psr4($map, (string) $namespace, rtrim($install_path, '/\\').'/'.trim((string) $dir, '/\\'));
                }
            }
        }
        ksort($map);

        return $map;
    }

    /**
     * @param array<class-string, string> $map
     */
    private static function scan_psr4(array &$map, string $namespace, string $base): void
    {
        if (! is_dir($base)) {
            return;
        }
        $prefix_len = strlen($base) + 1;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), $prefix_len));
            if ($relative === '' || str_starts_with(basename($relative), '_')) {
                continue;
            }
            $map[$namespace.str_replace('/', '\\', substr($relative, 0, -4))] = str_replace('\\', '/', $file->getPathname());
        }
    }

    /**
     * @param array<class-string, string> $classmap
     */
    public static function ensure_class(string $class, array $classmap): void
    {
        if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
            return;
        }
        $file = $classmap[$class] ?? null;
        if (is_string($file) && is_file($file)) {
            require_once $file;
        }
        class_exists($class, true);
    }
}
