<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Discovery;

/**
 * Data transfer object representing a package configuration file eligible for publishing.
 */
final class PublishableConfig
{
    /**
     * @param $key string Configuration section stem key.
     * @param $source string Absolute path to the package configuration file.
     * @param $target string Target path where the configuration file should be published.
     * @param $tag string|null Optional publishing tag for group filtering.
     * @param $package string|null Originating provider class or package identifier.
     */
    public function __construct(
        public readonly string $key,
        public readonly string $source,
        public readonly string $target,
        public readonly ?string $tag = null,
        public readonly ?string $package = null,
    ) {}

    /**
     * Serializes the publishable item to an associative array.
     *
     * @return array{key: string, source: string, target: string, tag: string|null, package: string|null}
     */
    public function to_array(): array
    {
        return [
            'key'     => $this->key,
            'source'  => $this->source,
            'target'  => $this->target,
            'tag'     => $this->tag,
            'package' => $this->package,
        ];
    }
}
