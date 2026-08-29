<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\Typography\TypographySystem;

final class TypographyTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        Config::boot();
    }

    /**
     * @return void
     */
    public function test_path_is_webapp_public(): void
    {
        $this->assertSame(
            webapp_path('public/fetch-fonts'),
            TypographySystem::path(TypographySystem::DIR),
        );
        $this->assertSame('latin', TypographySystem::pack('en'));
        $this->assertSame('latin', TypographySystem::pack('ru'));
        $this->assertSame('arabic', TypographySystem::pack('ar'));
        $this->assertSame('hebrew', TypographySystem::pack('he'));
        $this->assertSame('cjk', TypographySystem::pack('ja'));
        $this->assertSame('fetch-fonts/wts-fonts-latin.css', TypographySystem::fonts_css('latin'));
        $this->assertStringNotContainsString('Plex+Sans+Arabic', TypographySystem::google_css_url('en'));
        $this->assertStringContainsString('Plex+Sans+Arabic', TypographySystem::google_css_url('ar'));
        $this->assertArrayHasKey('dm-sans', TypographySystem::catalog());
    }
}
