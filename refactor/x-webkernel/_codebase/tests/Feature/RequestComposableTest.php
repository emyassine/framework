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
        unset($_SERVER['HTTP_USER_AGENT'], $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['HTTP_ACCEPT']);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        WebApp::flush();
        unset($_SERVER['HTTP_USER_AGENT'], $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['HTTP_ACCEPT']);
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
    public function test_user_agent_defaults_empty(): void
    {
        $this->assertSame('', (new RequestComposable())->user_agent());
    }

    /**
     * @return void
     */
    public function test_method_and_path(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/system/users?page=1';

        $request = new RequestComposable();
        $this->assertSame('POST', $request->method());
        $this->assertSame('/system/users', $request->path());
        $this->assertSame('/given', $request->path('/given?x=1'));
    }

    /**
     * @return void
     */
    public function test_header(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/html';

        $this->assertSame('text/html', (new RequestComposable())->header('Accept'));
        $this->assertSame('', (new RequestComposable())->header('X-Missing'));
    }
}
