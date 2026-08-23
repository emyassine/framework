<?php declare(strict_types=1);

namespace Webkernel\Config;

use Webkernel\App\Application;

/**
 * Global webapp helper function.
 * Returns the singleton Application instance.
 */
function webapp(): Application
{
    return Application::get_instance();
}
