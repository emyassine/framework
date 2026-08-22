<?php declare(strict_types=1);

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

use Webkernel\Json\CanonicalJson;

if (!function_exists('json_canonicalize')) {
    /**
     * Returns a fluent CanonicalJson object for the given value.
     *
     * Examples:
     *   json_canonicalize($data)->to_string()
     *   json_canonicalize($data)->hash(strategy: 'sha256')
     *   json_canonicalize($data)->hash(strategy: 'sha3-256')
     *   json_canonicalize($data)->hash(strategy: 'blake2b512')
     *   (string) json_canonicalize($data)
     *
     * @param  mixed  $data  Any JSON-serializable PHP value
     * @return CanonicalJson
     */
    function json_canonicalize(mixed $data): CanonicalJson
    {
        return CanonicalJson::of($data);
    }
}
