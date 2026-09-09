<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Imagery\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Imagery\Branding;

final class BrandingTest extends TestCase
{
    public function test_url_uses_webkernel_app_prefix(): void
    {
        $url = Branding::get()->url('webkernel-favicon');
        $this->assertStringStartsWith('/__webkernel-app/branding/webkernel/webkernel-favicon?v=', $url);
    }

    public function test_payload_returns_png_bytes(): void
    {
        $binary = Branding::get()->payload('webkernel', 'webkernel-favicon');
        $this->assertNotSame('', $binary);
        $this->assertSame("\x89PNG", \substr($binary, 0, 4));
    }

    public function test_unknown_key_is_empty(): void
    {
        $this->assertSame('', Branding::get()->url('not-a-brand-asset'));
    }
}
