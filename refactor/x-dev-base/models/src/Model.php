<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Models;

use Webkernel\Database\Database;
use Webkernel\Database\Query;

/**
 * One table row. Concrete models live in the package that owns the table.
 */
abstract class Model
{
    protected string $table = '';

    protected string $primary_key = 'id';

    /** @var array<string, mixed> */
    protected array $attributes = [];

    /** @var list<string> */
    protected array $hidden = [];

    protected bool $exists = false;

    /**
     * @param $attributes array<string, mixed>
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * @return string
     */
    public static function table(): string
    {
        $model = new static();
        if ($model->table !== '') {
            return $model->table;
        }
        $short = (new \ReflectionClass($model))->getShortName();
        $snake = \strtolower((string) \preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $short));

        return $snake.'s';
    }

    /**
     * @return Query
     */
    public static function query(): Query
    {
        return Database::table(static::table());
    }

    /**
     * @param $id int|string
     *
     * @return static|null
     */
    public static function find(int|string $id): ?static
    {
        $model = new static();
        $row = static::query()->where($model->primary_key, $id)->first();
        if ($row === null) {
            return null;
        }

        return $model->hydrate($row);
    }

    /**
     * @return list<static>
     */
    public static function all(): array
    {
        $out = [];
        foreach (static::query()->get() as $row) {
            $out[] = (new static())->hydrate($row);
        }

        return $out;
    }

    /**
     * @param $attributes array<string, mixed>
     *
     * @return static
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();

        return $model;
    }

    /**
     * @param $attributes array<string, mixed>
     *
     * @return static
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[(string) $key] = $value;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $key = $this->primary_key;
        if ($this->exists) {
            $values = $this->attributes;
            unset($values[$key]);
            static::query()->where($key, $this->attributes[$key] ?? null)->update($values);

            return true;
        }
        $id = static::query()->insert($this->attributes);
        if ($id > 0) {
            $this->attributes[$key] = $id;
        }
        $this->exists = true;

        return true;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        if (! $this->exists) {
            return false;
        }
        static::query()->where($this->primary_key, $this->attributes[$this->primary_key] ?? null)->delete();
        $this->exists = false;

        return true;
    }

    /**
     * @param $key string
     *
     * @return mixed
     */
    public function get_attribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function to_array(): array
    {
        $out = $this->attributes;
        foreach ($this->hidden as $key) {
            unset($out[$key]);
        }

        return $out;
    }

    /**
     * @param $name string
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->get_attribute($name);
    }

    /**
     * @param $name string
     * @param $value mixed
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param $row array<string, mixed>
     *
     * @return static
     */
    private function hydrate(array $row): static
    {
        $this->attributes = $row;
        $this->exists = true;

        return $this;
    }
}
