<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Request;

/**
 * Trusted reverse-proxy allowlist for forwarded client IP headers.
 */
final class TrustedProxies
{
    /** @var list<string> */
    private array $proxies;

    /**
     * @param $proxies list<string> Exact IPv4/IPv6 addresses or IPv4 CIDR (e.g. 10.0.0.0/8).
     */
    public function __construct(array $proxies = [])
    {
        $this->proxies = \array_values($proxies);
    }

    /**
     * @return bool
     */
    public function is_empty(): bool
    {
        return $this->proxies === [];
    }

    /**
     * @param $ip string
     * @return bool
     */
    public function is_trusted(string $ip): bool
    {
        if ($ip === '' || $this->proxies === []) {
            return false;
        }
        foreach ($this->proxies as $proxy) {
            if ($this->matches($ip, $proxy)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $ip string
     * @param $proxy string
     * @return bool
     */
    private function matches(string $ip, string $proxy): bool
    {
        if ($ip === $proxy) {
            return true;
        }
        if (! \str_contains($proxy, '/')) {
            return false;
        }

        // ponytail: IPv4 CIDR only — extend for IPv6 when deployments need it.
        if (! \filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            return false;
        }
        [$subnet, $bits] = \explode('/', $proxy, 2);
        if (! \filter_var($subnet, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) || ! \ctype_digit($bits)) {
            return false;
        }
        $mask = (int) $bits;
        if ($mask < 0 || $mask > 32) {
            return false;
        }
        $ip_long = \ip2long($ip);
        $subnet_long = \ip2long($subnet);
        if ($ip_long === false || $subnet_long === false) {
            return false;
        }
        if ($mask === 0) {
            return true;
        }
        $mask_long = -1 << (32 - $mask);

        return ($ip_long & $mask_long) === ($subnet_long & $mask_long);
    }
}
