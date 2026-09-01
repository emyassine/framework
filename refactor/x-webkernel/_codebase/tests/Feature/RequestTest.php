<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Composables\RequestComposable;
use Webkernel\Request;
use Webkernel\Request\TrustedProxies;
use Webkernel\WebApp;

final class RequestTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        WebApp::flush();
        Request::flush();
        Request::trust_proxies([]);
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_FILES = [];
        unset(
            $_SERVER['HTTP_USER_AGENT'],
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['HTTP_ACCEPT'],
            $_SERVER['CONTENT_TYPE'],
            $_SERVER['HTTP_AUTHORIZATION'],
            $_SERVER['REDIRECT_HTTP_AUTHORIZATION'],
            $_SERVER['HTTP_HTTP_X_REQUESTED_WITH'],
            $_SERVER['HTTP_X_REQUESTED_WITH'],
            $_SERVER['HTTPS'],
            $_SERVER['HTTP_X_FORWARDED_PROTO'],
            $_SERVER['HTTP_X_FORWARDED_FOR'],
            $_SERVER['HTTP_X_REAL_IP'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_HOST'],
            $_SERVER['SERVER_NAME'],
            $_SERVER['SERVER_PORT'],
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->setUp();
    }

    /**
     * @return void
     */
    public function test_user_agent_reads_server(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64)';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $request = Request::capture();
        $this->assertSame('Mozilla/5.0 (X11; Linux x86_64)', $request->user_agent());
        $this->assertSame('Mozilla/5.0 (X11; Linux x86_64)', \webapp()->request()->user_agent());
    }

    /**
     * @return void
     */
    public function test_method_path_and_query(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/files/my%20file.pdf?page=2';
        $_GET['page'] = '2';

        $request = Request::capture();
        $this->assertSame('POST', $request->method());
        $this->assertTrue($request->is_method('POST'));
        $this->assertFalse($request->is_method('GET'));
        $this->assertSame('/files/my file.pdf', $request->path());
        $this->assertSame('2', $request->query('page'));
        $this->assertSame(['page' => '2'], $request->query());
        $this->assertSame(['files', 'my file.pdf'], $request->segments());
        $this->assertSame('files', $request->segment(1));
    }

    /**
     * @return void
     */
    public function test_header_no_double_prefix_and_authorization_fallback(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Bearer token';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $request = Request::capture();
        $this->assertSame('XMLHttpRequest', $request->header('X-Requested-With'));
        $this->assertSame('XMLHttpRequest', $request->header('HTTP_X_REQUESTED_WITH'));
        $this->assertTrue($request->has_header('X-Requested-With'));
        $this->assertTrue($request->ajax());
        $this->assertSame('Bearer token', $request->header('Authorization'));
        $this->assertSame('token', $request->bearer_token());
        $this->assertSame('', $request->header('X-Missing'));
        $this->assertSame('fallback', $request->header('X-Missing', 'fallback'));
    }

    /**
     * @return void
     */
    public function test_input_post_and_is_json(): void
    {
        $request = Request::create(
            'POST',
            '/',
            [],
            ['name' => 'yassine'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
        );
        $this->assertFalse($request->is_json());
        $this->assertSame('yassine', $request->input('name'));
        $this->assertSame(['name' => 'yassine'], $request->input());
    }

    /**
     * @return void
     */
    public function test_json_body_is_instance_scoped(): void
    {
        $first = Request::create('POST', '/', [], [], [], [], [], '{"name":"first"}');
        $second = Request::create('POST', '/', [], [], [], [], [], '{"name":"second"}');

        $this->assertSame('first', $first->json('name'));
        $this->assertSame('second', $second->json('name'));
    }

    /**
     * @return void
     */
    public function test_cookie_files_client_and_scheme(): void
    {
        $request = Request::create(
            'GET',
            '/',
            [],
            [],
            ['session' => 'abc'],
            ['avatar' => ['name' => 'a.png', 'error' => 0]],
            [
                'HTTPS' => 'on',
                'HTTP_HOST' => 'app.test:8443',
                'REMOTE_ADDR' => '10.0.0.8',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.1, 10.0.0.1',
            ],
        );
        $this->assertSame('abc', $request->cookie('session'));
        $this->assertSame('a.png', $request->files('avatar')['name'] ?? null);
        $this->assertTrue($request->is_secure());
        $this->assertSame('https', $request->scheme());
        $this->assertSame('app.test', $request->host());
        $this->assertSame(8443, $request->port());
        $this->assertSame('10.0.0.8', $request->ip());
    }

    /**
     * @return void
     */
    public function test_forwarded_ip_requires_trusted_proxy(): void
    {
        $request = Request::create(
            'GET',
            '/',
            server: [
                'REMOTE_ADDR' => '10.0.0.8',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.1, 10.0.0.1',
            ],
        );
        $this->assertSame('10.0.0.8', $request->ip());

        Request::trust_proxies(['10.0.0.8']);
        $trusted = Request::create(
            'GET',
            '/',
            server: [
                'REMOTE_ADDR' => '10.0.0.8',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.1, 10.0.0.1',
            ],
        );

        $this->assertSame('203.0.113.1', $trusted->ip());
        $this->assertSame(['203.0.113.1', '10.0.0.1'], $trusted->ips());
    }

    /**
     * @return void
     */
    public function test_trusted_proxies_support_ipv4_cidr(): void
    {
        $proxies = new TrustedProxies(['10.0.0.0/8']);
        $this->assertTrue($proxies->is_trusted('10.0.0.8'));
        $this->assertFalse($proxies->is_trusted('203.0.113.1'));
    }

    /**
     * @return void
     */
    public function test_flush_clears_current_request(): void
    {
        $_SERVER['REQUEST_URI'] = '/first';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Request::capture();
        $_SERVER['REQUEST_URI'] = '/second';
        Request::flush();
        Request::capture();

        $this->assertSame('/second', Request::current()->path());
    }

    /**
     * @return void
     */
    public function test_composable_flush_delegates(): void
    {
        $_SERVER['REQUEST_URI'] = '/before';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Request::capture();
        RequestComposable::flush();
        $_SERVER['REQUEST_URI'] = '/after';
        Request::capture();

        $this->assertSame('/after', Request::current()->path());
    }
}
