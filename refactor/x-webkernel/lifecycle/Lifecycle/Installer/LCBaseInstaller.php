<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use RuntimeException;

/**
 * Custom installer for webkernel-* package types.
 *
 * @see https://getcomposer.org/doc/articles/custom-installers.md
 */
final class LCBaseInstaller extends LibraryInstaller
{
    private readonly LCInstallerLocations $locations;

    public function __construct(IOInterface $io, Composer $composer)
    {
        parent::__construct($io, $composer, 'library');
        $this->locations = new LCInstallerLocations($composer);
    }

    #[\Override]
    public function supports($packageType): bool
    {
        return in_array($packageType, $this->locations->types(), strict: true);
    }

    /**
     * Absolute install path. Must not end with a slash.
     */
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
            '{$name}' => $name,
        ];

        $type = LCPackageType::from($package->getType());
        if ($type->requires_parent_module()) {
            $parent = $this->locations->parent_module($package);
            if ($parent === null) {
                throw new RuntimeException(sprintf(
                    'Package "%s" has type "%s" and must declare its parent module via extra.webkernel.module in composer.json.',
                    $package->getName(),
                    $type->value,
                ));
            }
            $replacements['{$parentVendor}'] = $parent['vendor'];
            $replacements['{$parentName}'] = $parent['name'];
        }

        return rtrim(strtr($template, $replacements), '/\\');
    }
}
