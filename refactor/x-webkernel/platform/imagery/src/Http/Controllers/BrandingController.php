<?php declare(strict_types=1);

namespace Webkernel\Imagery\Http\Controllers;

use Webkernel\Imagery\Branding;

final class BrandingController
{
    public function show(string $brand, string $key): string
    {
        return Branding::get()->payload($brand, $key);
    }
}
