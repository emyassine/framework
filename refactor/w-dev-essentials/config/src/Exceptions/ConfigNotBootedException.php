<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Exceptions;

/**
 * Thrown when configuration access occurs before initialization.
 */
class ConfigNotBootedException extends ConfigException
{
    public function __construct()
    {
        parent::__construct(
            'Configuration system has not been booted. Call Config::boot() before accessing values.'
        );
    }
}
