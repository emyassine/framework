<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Installer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installers\Installer as ComposerInstaller;
use Composer\Package\PackageInterface;
use RuntimeException;

final class LCBaseInstaller extends ComposerInstaller
{
    private readonly LCInstallerLocations $locations;

    public function __construct(IOInterface $io, Composer $composer)
    {
        parent::__construct($io, $composer);
        $this->locations = new LCInstallerLocations($composer);
    }

    #[\Override]
    public function supports($packageType): bool
    {
        return in_array($packageType, $this->locations->types(), strict: true);
    }

    #[\Override]
    public function getInstallPath(PackageInterface $package): string
    {
        $template = $this->locations->destination($package);
        if ($template === null) {
            return parent::getInstallPath($package);
        }

        [$vendor, $name] = explode('/', $package->getName(), 2);
        $replacements = [
            '{$vendor}' => $vendor,
            '{$name}'   => $name,
        ];

        $type = LCPackageType::from($package->getType());
        if ($type->requiresParentModule()) {
            $parent = $this->locations->parentModule($package);
            if ($parent === null) {
                throw new RuntimeException(sprintf(
                    'Package "%s" has type "%s" and must declare its parent module via extra.webkernel.module in composer.json.',
                    $package->getName(),
                    $type->value,
                ));
            }
            $replacements['{$parentVendor}'] = $parent['vendor'];
            $replacements['{$parentName}']   = $parent['name'];
        }

        return strtr($template, $replacements);
    }
}
