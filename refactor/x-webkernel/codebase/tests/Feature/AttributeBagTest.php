<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\View\AttributeBag;
use Webkernel\View\View;

final class AttributeBagTest extends TestCase
{
    /**
     * @return void
     */
    public function test_stringifies_escaped_attributes_and_boolean_flags(): void
    {
        $html = (string) (new AttributeBag([
            'id' => 'save',
            'disabled' => true,
            'hidden' => false,
            'title' => 'Say "hi"',
        ]));

        $this->assertStringContainsString(' id="save"', $html);
        $this->assertStringContainsString(' disabled', $html);
        $this->assertStringNotContainsString('hidden', $html);
        $this->assertStringContainsString('title="Say &quot;hi&quot;"', $html);
    }

    /**
     * @return void
     */
    public function test_class_merges_conditional_list(): void
    {
        $html = (string) (new AttributeBag(['id' => 'b']))->class([
            'w-btn',
            'w-outlined' => true,
            'nope' => false,
        ]);

        $this->assertStringContainsString('w-btn', $html);
        $this->assertStringContainsString('w-outlined', $html);
        $this->assertStringNotContainsString('nope', $html);
    }

    /**
     * @return void
     */
    public function test_view_echo_does_not_double_escape_the_bag(): void
    {
        $bag = new AttributeBag(['title' => 'A & B']);
        $this->assertSame((string) $bag, View::echo($bag));
    }
}
