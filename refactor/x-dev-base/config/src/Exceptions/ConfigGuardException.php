<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Config\Exceptions;

/**
 * Thrown when a set() is attempted on a guarded (read-only) config key.
 */
class ConfigGuardException extends ConfigException
{
    public function __construct(string $key)
    {
        parent::__construct(
            \sprintf('Config key "%s" is protected and cannot be mutated at runtime.', $key)
        );
    }
}
