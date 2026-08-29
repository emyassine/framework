<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Resources;

use Webkernel\Platform\Schemas\Schema;
use Webkernel\Platform\Tables\Table;

abstract class Resource
{
    protected static string $model = '';

    public static string $slug = '';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    /**
     * @return list<class-string>
     */
    public static function relations(): array
    {
        return [];
    }

    /**
     * @return array<string, class-string|array{class: class-string, path: string, methods?: list<string>}>
     */
    public static function pages(): array
    {
        return [];
    }
}
