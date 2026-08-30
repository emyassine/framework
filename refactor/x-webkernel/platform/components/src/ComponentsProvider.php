<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Webkernel\PlatformProvider;

/**
 * UI atoms. Views live next to their CSS. Page chrome is only page / page.base / page.simple.
 *
 * //> Regions are siblings of page, not nested page.* tags: rail, sidebar, topbar, main, aside.
 */
final class ComponentsProvider extends PlatformProvider
{
    public const VIEWS = [
        'webkernel' => __DIR__.'/../resources/views',
    ];

    public const COMPONENTS = [
        'webkernel' => __DIR__.'/../resources/views',
    ];

    /**
     * Dual-use atoms: Blade tag plus `::make()`.
     *
     * @return list<class-string>
     */
    public static function atoms(): array
    {
        return [
            Avatar::class,
            Badge::class,
            Breadcrumbs::class,
            Button::class,
            ButtonGroup::class,
            Callout::class,
            Checkbox::class,
            Dropdown::class,
            EmptyState::class,
            Fieldset::class,
            IconButton::class,
            Input::class,
            InputWrapper::class,
            Link::class,
            LoadingIndicator::class,
            Modal::class,
            Pagination::class,
            Section::class,
            Select::class,
            Tabs::class,
            TabsItem::class,
            TabsPanel::class,
            TextInput::class,
        ];
    }
}
