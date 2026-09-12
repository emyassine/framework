<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Composables;

use Webkernel\Request;

/**
 * Composable facade for the current {@see Request}. `webapp()->request()`.
 *
 * @method string method()
 * @method bool is_method(string $method)
 * @method string path(?string $uri = null)
 * @method list<string> segments()
 * @method string|null segment(int $index, ?string $default = null)
 * @method mixed query(?string $key = null, mixed $default = null)
 * @method mixed input(?string $key = null, mixed $default = null)
 * @method mixed json(?string $key = null, mixed $default = null)
 * @method mixed cookie(?string $key = null, mixed $default = null)
 * @method mixed files(?string $key = null)
 * @method string header(string $name, string $default = '')
 * @method bool has_header(string $name)
 * @method bool is_json()
 * @method bool wants_json()
 * @method bool ajax()
 * @method bool is_secure()
 * @method string scheme()
 * @method string host()
 * @method int port()
 * @method string ip()
 * @method list<string> ips()
 * @method string user_agent()
 * @method string url()
 * @method string full_url()
 * @method string|null bearer_token()
 */
final class RequestComposable implements ComposableContract
{
    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'request';
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        Request::flush();
    }

    /**
     * @return Request
     */
    public function instance(): Request
    {
        return Request::current();
    }

    /**
     * @param $name string
     * @param $arguments list<mixed>
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return Request::current()->{$name}(...$arguments);
    }
}
