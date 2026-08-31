<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Composables\RequestComposable;
use Webkernel\WebApp;

final class RequestComposableTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        WebApp::flush();
        RequestComposable::flush();
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

        $this->assertSame('Mozilla/5.0 (X11; Linux x86_64)', (new RequestComposable())->user_agent());
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

        $request = new RequestComposable();
        $this->assertSame('POST', $request->method());
        $this->assertTrue($request->is_method('POST'));
        $this->assertFalse($request->is_method('GET'));
        $this->assertSame('/files/my file.pdf', $request->path());
        $this->assertSame('2', $request->query('page'));
        $this->assertSame(['page' => '2'], $request->query());
    }

    /**
     * @return void
     */
    public function test_header_no_double_prefix_and_authorization_fallback(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Bearer token';

        $request = new RequestComposable();
        $this->assertSame('XMLHttpRequest', $request->header('X-Requested-With'));
        $this->assertSame('XMLHttpRequest', $request->header('HTTP_X_REQUESTED_WITH'));
        $this->assertTrue($request->has_header('X-Requested-With'));
        $this->assertSame('Bearer token', $request->header('Authorization'));
        $this->assertSame('', $request->header('X-Missing'));
        $this->assertSame('fallback', $request->header('X-Missing', 'fallback'));
    }

    /**
     * @return void
     */
    public function test_input_post_and_is_json(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_POST['name'] = 'yassine';

        $request = new RequestComposable();
        $this->assertFalse($request->is_json());
        $this->assertSame('yassine', $request->input('name'));
        $this->assertSame(['name' => 'yassine'], $request->input());
    }

    /**
     * @return void
     */
    public function test_cookie_files_client_and_scheme(): void
    {
        $_COOKIE['session'] = 'abc';
        $_FILES['avatar'] = ['name' => 'a.png', 'error' => 0];
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'app.test:8443';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.8';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1, 10.0.0.1';

        $request = new RequestComposable();
        $this->assertSame('abc', $request->cookie('session'));
        $this->assertSame('a.png', $request->files('avatar')['name'] ?? null);
        $this->assertTrue($request->is_secure());
        $this->assertSame('https', $request->scheme());
        $this->assertSame('app.test', $request->host());
        $this->assertSame(8443, $request->port());
        $this->assertSame('203.0.113.1', $request->ip());
    }
}
