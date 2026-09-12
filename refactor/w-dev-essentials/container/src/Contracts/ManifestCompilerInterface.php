<?php declare(strict_types=1);

namespace Webkernel\Contracts;

use Webkernel\Container\Manifest\ContainerManifest;

interface ManifestCompilerInterface
{
    public function isStale(): bool;

    public function rebuild(): ContainerManifest;

    public function load(): ContainerManifest;
}
