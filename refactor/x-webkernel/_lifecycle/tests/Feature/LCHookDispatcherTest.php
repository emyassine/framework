<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Lifecycle\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Lifecycle\Hook\LCHook;
use Webkernel\Lifecycle\Hook\LCHookDispatcher;

final class LCHookDispatcherTest extends TestCase
{
    protected function setUp(): void
    {
        HookProbe::$log = [];
    }

    /**
     * @return void
     */
    public function test_extra_key_matches_composer_event_name(): void
    {
        $this->assertSame('post_autoload_dump', LCHook::PostAutoloadDump->extra_key());
        $this->assertSame('pre_install_cmd', LCHook::PreInstallCmd->extra_key());
    }

    /**
     * @return void
     */
    public function test_codebase_hook_runs_before_other_packages(): void
    {
        $dispatcher = new LCHookDispatcher();
        $dispatcher->dispatch('post-autoload-dump', new \stdClass(), [
            ['name' => 'acme/billing', 'extra' => ['post_autoload_dump' => HookProbe::class.'::billing']],
            ['name' => 'webkernel/codebase', 'extra' => ['post_autoload_dump' => HookProbe::class.'::codebase']],
        ]);

        $this->assertSame(['codebase', 'billing'], HookProbe::$log);
    }
}

final class HookProbe
{
    /** @var list<string> */
    public static array $log = [];

    /**
     * @return void
     */
    public static function codebase(): void
    {
        self::$log[] = 'codebase';
    }

    /**
     * @return void
     */
    public static function billing(): void
    {
        self::$log[] = 'billing';
    }
}
