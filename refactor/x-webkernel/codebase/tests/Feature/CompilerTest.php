<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\View\Compile\Directives;
use Webkernel\View\Compiler;
use Webkernel\View\View;

/**
 * @method void assertStringContainsString(string $needle, string $haystack, string $message = '')
 * @method void assertStringNotContainsString(string $needle, string $haystack, string $message = '')
 * @method void assertTrue(bool $condition, string $message = '')
 * @method void assertFalse(bool $condition, string $message = '')
 * @method void assertEquals(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNotEquals(mixed $expected, mixed $actual, string $message = '')
 * @method void assertSame(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNotSame(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNull(mixed $actual, string $message = '')
 * @method void assertNotNull(mixed $actual, string $message = '')
 * @method void assertEmpty(mixed $actual, string $message = '')
 * @method void assertNotEmpty(mixed $actual, string $message = '')
 * @method void assertCount(int $expectedCount, Countable|array $haystack, string $message = '')
 * @method void assertInstanceOf(string $expectedClass, mixed $actual, string $message = '')
 * @method void assertNotInstanceOf(string $expectedClass, mixed $actual, string $message = '')
 * @method void assertContains(mixed $needle, iterable $haystack, string $message = '')
 * @method void assertNotContains(mixed $needle, iterable $haystack, string $message = '')
 * @method void assertGreaterThan(mixed $expected, mixed $actual, string $message = '')
 * @method void assertGreaterThanOrEqual(mixed $expected, mixed $actual, string $message = '')
 * @method void assertLessThan(mixed $expected, mixed $actual, string $message = '')
 * @method void assertLessThanOrEqual(mixed $expected, mixed $actual, string $message = '')
 */
final class CompilerTest extends TestCase
{
    /**
     * @return void
     */
    public function test_compile_string_echo_if_foreach_and_extends(): void
    {
        $compiler = new Compiler(new Directives(), '\\'.View::class.'::echo(%s)');
        $php = $compiler->compile_string(
            "@extends('webkernel::layouts.page')\n"
            ."@section('title', 'Hi')\n"
            ."@if (true)\n{{ \$name }}\n@endif\n"
            ."@foreach (\$rows as \$row)\n{{ \$row }}\n@endforeach\n"
            .'{!! $raw !!}'."\n"
            .'@endsection'."\n"
        );

        $this->assertStringContainsString('$_shouldextend[', $php);
        $this->assertStringContainsString("start_section('title', 'Hi')", $php);
        $this->assertStringContainsString('if(true):', $php);
        $this->assertStringContainsString('foreach($rows as $row):', $php);
        $this->assertStringContainsString('endforeach;', $php);
        $this->assertStringNotContainsString('add_loop', $php);
        $this->assertStringContainsString('View::echo($name)', $php);
        $this->assertStringContainsString('echo $raw;', $php);
        $this->assertStringContainsString("run_child('webkernel::layouts.page')", $php);
    }

    /**
     * @return void
     */
    public function test_custom_if_registers_runtime_check(): void
    {
        $directives = new Directives();
        $directives->condition('env', static fn (string $e): bool => $e === 'prod');
        $compiler = new Compiler($directives);
        $php = $compiler->compile_string("@env('prod') yes @endenv");

        $this->assertStringContainsString("check('env', 'prod')", $php);
        $this->assertTrue($directives->check('env', 'prod'));
        $this->assertFalse($directives->check('env', 'dev'));
    }

    /**
     * @return void
     */
    public function test_component_boolean_and_kebab_attributes(): void
    {
        $compiler = new Compiler(new Directives(), '\\'.View::class.'::echo(%s)');
        $php = $compiler->compile_string('<x-webkernel::button outlined icon-position="after" class="x">Go</x-webkernel::button>');

        $this->assertStringContainsString("'outlined'=>true", $php);
        $this->assertStringContainsString("'icon_position'=>'after'", $php);
        $this->assertStringContainsString('start_component', $php);
    }

    /**
     * @return void
     */
    public function test_props_builds_attribute_bag_except(): void
    {
        $compiler = new Compiler(new Directives(), '\\'.View::class.'::echo(%s)');
        $php = $compiler->compile_string("@props(['color' => 'primary'])");

        $this->assertStringContainsString('$__props =', $php);
        $this->assertStringContainsString('AttributeBag', $php);
        $this->assertStringContainsString('except', $php);
    }
}
