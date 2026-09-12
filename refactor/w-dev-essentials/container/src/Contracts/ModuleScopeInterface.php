<?php declare(strict_types=1);

namespace Webkernel\Contracts;

interface ModuleScopeInterface
{
    public function moduleId(): string;

    /**
     * @return list<class-string>
     */
    public function exports(): array;

    public function resolve(string $abstract): object;

    public function owns(string $abstract): bool;
}
