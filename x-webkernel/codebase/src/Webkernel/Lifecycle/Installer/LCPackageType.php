<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Installer;

enum LCPackageType: string
{
    // --- Infrastructure & Core ---
    case Assets          = 'webkernel-assets';
    case Component       = 'webkernel-component';
    case DevTool         = 'webkernel-devtool';
    case Stdlib          = 'webkernel-stdlib';
    case Element         = 'webkernel-element';
    case Agent           = 'webkernel-agent';
    case Ffi             = 'webkernel-ffi';

    // --- Business Architecture ---
    case BusinessModule         = 'webkernel-business-module';
    case BusinessModuleFeature  = 'webkernel-business-module-feature';

    // --- Platform Architecture ---
    case PlatformModule         = 'webkernel-platform-module';
    case PlatformModuleFeature  = 'webkernel-platform-module-feature';

    /**
     * Get the description of the package type.
     */
    public function description(): string
    {
        return match ($this) {
            self::Assets                 => 'Asset bundle.',
            self::DevTool                => 'Development tool package.',
            self::Stdlib                 => 'Standard library package.',
            self::Component              => 'Components foundational capability.',
            self::Element                => 'Reusable UI component or Filament element.',
            self::Agent                  => 'Agentic worker package.',
            self::Ffi                    => 'Native binary bridged through the Webkernel ABI.',

            // Business
            self::BusinessModule         => 'Autonomous business domain module.',
            self::BusinessModuleFeature  => 'Feature attached to a parent business module. Requires extra.webkernel.module.',

            // Platform
            self::PlatformModule         => 'Core platform or technical infrastructure module.',
            self::PlatformModuleFeature  => 'Feature attached to a parent platform module. Requires extra.webkernel.module.',
        };
    }

    /**
     * Package types that must declare a parent module via
     * extra.webkernel.module in their composer.json.
     */
    public function requiresParentModule(): bool
    {
        return match ($this) {
            self::BusinessModuleFeature, self::PlatformModuleFeature => true,
            default => false,
        };
    }
}
