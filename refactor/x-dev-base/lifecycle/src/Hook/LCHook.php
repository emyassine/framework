<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Hook;

/**
 * Enumeration of all Composer script events supported by the Webkernel lifecycle plugin.
 *
 * Each case maps to the exact Composer event name (its string value).
 * Packages declare their callables under extra.webkernel.lifecycle.events.{value}.
 */
enum LCHook: string
{
    case PreInstallCmd           = 'pre-install-cmd';
    case PostInstallCmd          = 'post-install-cmd';
    case PreUpdateCmd            = 'pre-update-cmd';
    case PostUpdateCmd           = 'post-update-cmd';
    case PreAutoloadDump         = 'pre-autoload-dump';
    case PostAutoloadDump        = 'post-autoload-dump';
    case PostRootPackageInstall  = 'post-root-package-install';
    case PostCreateProjectCmd    = 'post-create-project-cmd';

    /**
     * The key used in extra.webkernel.lifecycle.events.{key}.
     */
    public function extra_key(): string
    {
        return $this->value;
    }
}
