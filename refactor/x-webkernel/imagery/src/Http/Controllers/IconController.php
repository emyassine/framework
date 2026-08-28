<?php

declare(strict_types=1);

namespace Webkernel\Imagery\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkernel\Imagery\IconSetManager;

/**
 * Paginated icon list for the imagery picker UI.
 */
final class IconController extends Controller
{
    public function __invoke(Request $request, IconSetManager $manager): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $per_page = (int) $request->get('per_page', config('imagery.icons_per_page', 100));
        $search = $request->get('search');
        $set_filter = $request->get('set');
        $allowed_sets = $request->get('allowed_sets');

        if (is_string($allowed_sets)) {
            $allowed_sets = explode(',', $allowed_sets);
        }

        $result = $manager->get_icons_paginated(
            page: $page,
            per_page: $per_page,
            search: is_string($search) ? $search : null,
            set_filter: is_string($set_filter) ? $set_filter : null,
            allowed_sets: is_array($allowed_sets) ? $allowed_sets : null,
        );

        $icons_with_svg = $result['icons']->map(function (array $icon): array {
            $icon['svg'] = $this->icon_svg($icon['name']);

            return $icon;
        });

        return response()->json([
            'icons' => $icons_with_svg->toArray(),
            'has_more' => $result['has_more'],
            'total' => $result['total'],
            'page' => $page,
        ]);
    }

    /**
     * SVG markup for one icon name, with a consistent size.
     */
    protected function icon_svg(string $icon_name): string
    {
        try {
            $svg = svg($icon_name)->toHtml();

            return preg_replace(
                '/<svg([^>]*)>/',
                '<svg$1 style="width: 1.5rem; height: 1.5rem;">',
                $svg
            ) ?: $svg;
        } catch (\Throwable) {
            return '<svg xmlns="http://www.w3.org/2000/svg" style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>';
        }
    }
}
