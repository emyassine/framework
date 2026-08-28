<?php

declare(strict_types=1);

namespace Webkernel\Imagery\Facades;

use Illuminate\Support\Facades\Facade;
use Webkernel\Imagery\IconSetManager;

/**
 * @method static array get_sets()
 * @method static array get_set_names()
 * @method static \Illuminate\Support\Collection get_icons(?array $allowed_sets = null)
 * @method static \Illuminate\Support\Collection search_icons(string $query, ?array $allowed_sets = null, ?string $set_filter = null)
 * @method static array get_icons_paginated(int $page = 1, int $per_page = 100, ?string $search = null, ?string $set_filter = null, ?array $allowed_sets = null)
 * @method static void clear_cache()
 * @method static array get_icons_for_set(string $set_name)
 *
 * @see IconSetManager
 */
final class Imagery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IconSetManager::class;
    }
}
