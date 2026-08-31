<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

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
            'modules/{$vendor}/{$name}' => [LCPackageType::BusinessModule],
            'modules/{$parentVendor}/{$parentName}/features/{$vendor}-{$name}' => [LCPackageType::BusinessModuleFeature],
            'ffi/{$vendor}/{$name}' => [LCPackageType::Ffi],
            $vendor_dir.'/{$vendor}/{$name}' => [
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
        return array_map(static fn (LCPackageType $t): string => $t->value, LCPackageType::cases());
    }

    public function destination(PackageInterface $package): ?string
    {
        $type = LCPackageType::tryFrom($package->getType());
        if ($type === null) {
            return null;
        }

        foreach ($this->templates as $template => $package_types) {
            if (in_array($type, $package_types, strict: true)) {
                return $template;
            }
        }

        return null;
    }

    /**
     * @return array{vendor: string, name: string}|null
     */
    public function parent_module(PackageInterface $package): ?array
    {
        $module = $package->getExtra()['webkernel']['module'] ?? null;
        if (! is_string($module) || ! str_contains($module, '/')) {
            return null;
        }

        [$vendor, $name] = explode('/', $module, 2);

        return ['vendor' => $vendor, 'name' => $name];
    }
}
