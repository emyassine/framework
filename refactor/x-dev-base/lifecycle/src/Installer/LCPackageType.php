<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Installer;

/**
 * All package types managed by the Webkernel custom installer.
 *
 * The string value is the exact "type" field in composer.json of the managed package.
 */
enum LCPackageType: string
{
    case Assets                = 'webkernel-assets';
    case Component             = 'webkernel-component';
    case DevTool               = 'webkernel-devtool';
    case Stdlib                = 'webkernel-stdlib';
    case Element               = 'webkernel-element';
    case Agent                 = 'webkernel-agent';
    case Ffi                   = 'webkernel-ffi';
    case BusinessModule        = 'webkernel-business-module';
    case BusinessModuleFeature = 'webkernel-business-module-feature';
    case PlatformModule        = 'webkernel-platform-module';
    case PlatformModuleFeature = 'webkernel-platform-module-feature';

    public function description(): string
    {
        return match ($this) {
            self::Assets                => 'Asset bundle.',
            self::DevTool               => 'Development tool package.',
            self::Stdlib                => 'Standard library package.',
            self::Component             => 'Foundational component.',
            self::Element               => 'Reusable UI element.',
            self::Agent                 => 'Agent worker package.',
            self::Ffi                   => 'Native binary bridged through the Webkernel ABI.',
            self::BusinessModule        => 'Autonomous business domain module.',
            self::BusinessModuleFeature => 'Feature attached to a parent business module. Requires extra.webkernel.module.',
            self::PlatformModule        => 'Core platform module.',
            self::PlatformModuleFeature => 'Feature attached to a parent platform module. Requires extra.webkernel.module.',
        };
    }

    public function requires_parent_module(): bool
    {
        return match ($this) {
            self::BusinessModuleFeature,
            self::PlatformModuleFeature => true,
            default                     => false,
        };
    }
}
