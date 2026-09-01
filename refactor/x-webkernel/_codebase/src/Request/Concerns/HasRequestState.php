<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request\Concerns;

/**
 * Shared request bags on {@see \Webkernel\Request}. Annotation-only; no runtime members.
 *
 * @property array<string, mixed> $query
 * @property array<string, mixed> $request
 * @property array<string, mixed> $cookies
 * @property array<string, mixed> $files
 * @property array<string, mixed> $server
 * @property array<string, string> $headers
 * @property array<string, mixed> $attributes
 * @property string $content
 * @property string $method
 * @property string $path_info
 * @property array<string, mixed>|null $json_cache
 */
trait HasRequestState
{
}
