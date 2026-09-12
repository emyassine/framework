<?php declare(strict_types=1);

namespace Webkernel\Contracts;

abstract class ServiceProvider
{
    /** @var list<string> */
    public const ROUTES = [];

    /** @var array<string, string|list<string>> */
    public const VIEWS = [];

    /** @var array<string, string|list<string>> */
    public const COMPONENTS = [];

    /** @var list<string> */
    public const LANG_PATH = [];

    /** @var list<class-string> */
    public const COMMANDS = [];

    /** @var array<string, array{path:string, publish:string, tag?:string}>|list<string> */
    public const CONFIG = [];

    /** @var list<string> */
    public const MIGRATIONS = [];

    /** @var list<class-string> */
    public const PANELS = [];

    /** @var list<class-string> */
    public const EXPORTS = [];

    public function __construct(
        protected RestrictedContainerInterface $container,
    ) {}

    public function register(): void {}

    public function boot(): void {}
}
