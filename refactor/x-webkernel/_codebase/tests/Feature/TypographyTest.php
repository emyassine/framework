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
        $this->assertSame(
            ['https://fonts.gstatic.com/s/dmsans/v1/abc.woff2' => '/fetch-fonts/dm-sans/abc.woff2'],
            TypographySystem::woff2_targets(
                '@font-face{src:url(https://fonts.gstatic.com/s/dmsans/v1/abc.woff2) format("woff2")}',
                'dm-sans',
            ),
        );
        $this->assertSame(
            ['https://fonts.gstatic.com/s/sc/v1/hash.100.woff2' => '/fetch-fonts/noto-sans-sc/hash.100.woff2'],
            TypographySystem::woff2_targets(
                'src:url(https://fonts.gstatic.com/s/sc/v1/hash.100.woff2)',
                'noto-sans-sc',
            ),
        );
        $this->assertSame([], TypographySystem::woff2_targets('@import url(https://fonts.googleapis.com/css2?family=DM+Sans);', 'dm-sans'));
    }
}
