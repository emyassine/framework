<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Installer;

use Composer\Composer;
use Composer\Package\PackageInterface;

final readonly class LCInstallerLocations
{
    /** @var array<string, list<LCPackageType>> */
    private array $templates;

    public function __construct(Composer $composer)
    {
        $vendor_dir = rtrim((string) $composer->getConfig()->get('vendor-dir'), '/');

        $this->templates = [
            'modules/{$vendor}/{$name}/'                                        => [LCPackageType::BusinessModule],
            'modules/{$parentVendor}/{$parentName}/features/{$vendor}-{$name}/' => [LCPackageType::BusinessModuleFeature],
            'ffi/{$vendor}/{$name}/'                                            => [LCPackageType::Ffi],
            $vendor_dir . '/{$vendor}/{$name}/' => [
                LCPackageType::Assets,
                LCPackageType::Component,
                LCPackageType::DevTool,
                LCPackageType::Stdlib,
                LCPackageType::Element,
                LCPackageType::Agent,
            ],
        ];
    }

    /** @return list<string> */
    public function types(): array
    {
        return array_map(static fn (LCPackageType $t) => $t->value, LCPackageType::cases());
    }

    public function destination(PackageInterface $package): ?string
    {
        $type = LCPackageType::tryFrom($package->getType());
        if ($type === null) {
            return null;
        }

        foreach ($this->templates as $template => $packageTypes) {
            if (in_array($type, $packageTypes, strict: true)) {
                return $template;
            }
        }

        return null;
    }

    public function parentModule(PackageInterface $package): ?array
    {
        $module = $package->getExtra()['webkernel']['module'] ?? null;
        if (!is_string($module) || !str_contains($module, '/')) {
            return null;
        }

        [$vendor, $name] = explode('/', $module, 2);

        return ['vendor' => $vendor, 'name' => $name];
    }
}
