<?php declare(strict_types=1);

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
