<?php declare(strict_types=1);

namespace Webkernel\Container\Manifest;

use ReflectionClass;
use Webkernel\Contracts\ManifestCompilerInterface;
use Webkernel\Contracts\ServiceProvider;

final readonly class ManifestCompiler implements ManifestCompilerInterface
{
    /**
     * @param array<string, class-string<ServiceProvider>> $providers
     * @param list<string> $watchedFiles
     */
    public function __construct(
        private string $manifestFile,
        private array $providers = [],
        private array $watchedFiles = [],
    ) {}

    public function isStale(): bool
    {
        if (! \is_file($this->manifestFile)) {
            return true;
        }

        $manifest = $this->load();

        foreach ($this->sourceMtimes() as $file => $mtime) {
            if (($manifest->mtimes[$file] ?? null) !== $mtime) {
                return true;
            }
        }

        return false;
    }

    public function rebuild(): ContainerManifest
    {
        $manifest = $this->compile();
        $this->write($manifest);

        return $manifest;
    }

    public function load(): ContainerManifest
    {
        $payload = \is_file($this->manifestFile) ? require $this->manifestFile : [];

        return ContainerManifest::fromArray(\is_array($payload) ? $payload : []);
    }

    private function compile(): ContainerManifest
    {
        $modules = [];

        foreach ($this->providers as $moduleId => $provider) {
            if (! \class_exists($provider)) {
                continue;
            }

            $reflection = new ReflectionClass($provider);

            $modules[$moduleId] = [
                'provider' => $provider,
                'exports' => $this->constant($reflection, 'EXPORTS'),
                'routes' => $this->constant($reflection, 'ROUTES'),
                'views' => $this->constant($reflection, 'VIEWS'),
                'components' => $this->constant($reflection, 'COMPONENTS'),
                'lang_path' => $this->constant($reflection, 'LANG_PATH'),
                'commands' => $this->constant($reflection, 'COMMANDS'),
                'config' => $this->constant($reflection, 'CONFIG'),
                'migrations' => $this->constant($reflection, 'MIGRATIONS'),
                'panels' => $this->constant($reflection, 'PANELS'),
            ];
        }

        return new ContainerManifest(
            modules: $modules,
            bindings: [],
            lazyProxies: [],
            mtimes: $this->sourceMtimes(),
        );
    }

    private function write(ContainerManifest $manifest): void
    {
        $directory = \dirname($this->manifestFile);

        if (! \is_dir($directory) && ! @\mkdir($directory, 0775, true) && ! \is_dir($directory)) {
            throw new \RuntimeException(\sprintf('Unable to create manifest directory: "%s".', $directory));
        }

        $payload = \var_export($manifest->toArray(), true);
        $code = "<?php declare(strict_types=1);\n\nreturn {$payload};\n";
        $temporaryFile = $this->manifestFile . '.' . \bin2hex(\random_bytes(4)) . '.tmp';

        if (@\file_put_contents($temporaryFile, $code, \LOCK_EX) === false) {
            throw new \RuntimeException(\sprintf('Unable to write manifest file: "%s".', $temporaryFile));
        }

        if (! @\rename($temporaryFile, $this->manifestFile)) {
            @\unlink($temporaryFile);
            throw new \RuntimeException(\sprintf('Unable to move manifest into place: "%s".', $this->manifestFile));
        }

        if (\function_exists('opcache_invalidate')) {
            @\opcache_invalidate($this->manifestFile, true);
        }
    }

    /**
     * @return array<string, int>
     */
    private function sourceMtimes(): array
    {
        $files = $this->watchedFiles;

        foreach ($this->providers as $provider) {
            if (\class_exists($provider)) {
                $files[] = (string) (new ReflectionClass($provider))->getFileName();
            }
        }

        $mtimes = [];

        foreach (\array_values(\array_unique($files)) as $file) {
            if (\is_file($file)) {
                $mtimes[$file] = (int) \filemtime($file);
            }
        }

        return $mtimes;
    }

    /**
     * @return mixed
     */
    private function constant(ReflectionClass $reflection, string $name): mixed
    {
        return $reflection->hasConstant($name) ? $reflection->getConstant($name) : [];
    }
}
