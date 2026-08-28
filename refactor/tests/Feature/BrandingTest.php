<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Imagery\Branding;

final class BrandingTest extends TestCase
{
    public function test_url_uses_webkernel_app_prefix(): void
    {
        $url = Branding::get()->url('webkernel-favicon');
        $this->assertStringStartsWith('/__webkernel-app/branding/webkernel/webkernel-favicon?v=', $url);
    }

    public function test_show_returns_png_bytes(): void
    {
        $binary = Branding::get()->show('webkernel', 'webkernel-favicon');
        $this->assertNotSame('', $binary);
        $this->assertSame("\x89PNG", substr($binary, 0, 4));
    }

    public function test_unknown_key_is_empty(): void
    {
        $this->assertSame('', Branding::get()->url('not-a-brand-asset'));
    }
}
