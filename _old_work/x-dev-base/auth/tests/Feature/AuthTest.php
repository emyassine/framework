<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Auth\Auth;
use Webkernel\Auth\Hash;
use Webkernel\Auth\Http\Middleware\Authenticate;
use Webkernel\Auth\User;
use Webkernel\Config\Config;
use Webkernel\Database\Database;
use Webkernel\Database\Migrator;
use Webkernel\View\Compile\Directives;
use Webkernel\View\Compiler;
use Webkernel\View\View;

final class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        Database::flush();
        Auth::flush();
        View::flush();
        $_SESSION = [];
        Database::connect(['driver' => 'sqlite', 'database' => ':memory:'], 'testing');
        (new Migrator())->run([
            \dirname(__DIR__, 2).'/database/migrations',
        ]);
    }

    /**
     * @return void
     */
    public function test_attempt_login_and_logout(): void
    {
        User::create([
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => Hash::make('secret'),
        ]);
        $auth = Auth::get();
        $this->assertFalse($auth->check());
        $this->assertTrue($auth->attempt('ada@example.com', 'secret'));
        $this->assertTrue($auth->check());
        $this->assertSame('Ada', $auth->user()?->get_name());
        $this->assertFalse($auth->attempt('ada@example.com', 'nope'));
        $auth->logout();
        $this->assertTrue($auth->guest());
    }

    /**
     * @return void
     */
    public function test_authenticate_middleware_redirects_guests(): void
    {
        $out = (new Authenticate())->handle(static fn (): string => 'ok');
        $this->assertSame('', $out);

        Auth::get()->acting_as(new User(['id' => 1, 'name' => 'Ada', 'email' => 'ada@example.com']));
        $this->assertSame('ok', (new Authenticate())->handle(static fn (): string => 'ok'));
    }

    /**
     * @return void
     */
    public function test_auth_directive_compiles_to_helper_check(): void
    {
        $compiler = new Compiler(new Directives(), '\\'.View::class.'::echo(%s)');
        $php = $compiler->compile_string("@auth()\nIN\n@endauth\n@guest\nOUT\n@endguest\n");
        $this->assertStringContainsString("function_exists('auth') && auth()->check()", $php);
        $this->assertStringContainsString("! \\function_exists('auth') || ! auth()->check()", $php);
    }

    /**
     * @return void
     */
    public function test_login_view_renders(): void
    {
        $html = View::make('webkernel::login', ['error' => 'Nope', 'email' => 'ada@example.com'])->render();
        $this->assertStringContainsString('Sign in', $html);
        $this->assertStringContainsString('Nope', $html);
        $this->assertStringContainsString('ada@example.com', $html);
        $this->assertStringContainsString('csrf-token', $html);
    }
}
