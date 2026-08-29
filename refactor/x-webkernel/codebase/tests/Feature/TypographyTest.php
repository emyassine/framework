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
            webapp_path('public/'.TypographySystem::FONTS_CSS),
            TypographySystem::path(TypographySystem::FONTS_CSS),
        );
        $this->assertSame(
            webapp_path('public/fetch-fonts'),
            TypographySystem::path(TypographySystem::DIR),
        );
        $this->assertArrayHasKey('dm-sans', TypographySystem::catalog());
    }
}
