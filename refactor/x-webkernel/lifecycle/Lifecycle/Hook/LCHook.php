<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Lifecycle\Hook;

/**
 * Composer script events a Webkernel package may hook via extra.webkernel.{key}.
 *
 * //> Key is the Composer event name with hyphens turned to underscores.
 * //> Example: extra.webkernel.post_autoload_dump = FQCN or Class::method
 */
enum LCHook: string
{
    case PreInstallCmd = 'pre-install-cmd';
    case PostInstallCmd = 'post-install-cmd';
    case PreUpdateCmd = 'pre-update-cmd';
    case PostUpdateCmd = 'post-update-cmd';
    case PreAutoloadDump = 'pre-autoload-dump';
    case PostAutoloadDump = 'post-autoload-dump';
    case PostRootPackageInstall = 'post-root-package-install';
    case PostCreateProjectCmd = 'post-create-project-cmd';

    /**
     * @return string
     */
    public function extra_key(): string
    {
        return \str_replace('-', '_', $this->value);
    }
}
