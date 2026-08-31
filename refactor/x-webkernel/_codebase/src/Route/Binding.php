<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Route;

use Webkernel\Route\Compile\Generator;
use Webkernel\Route\Compile\Pattern;
use Webkernel\Route\Exception\BadRoute;
use Webkernel\Route\Group\PendingGroup;

/**
 * One registered route. Returned by Route::get / view / … so attributes chain.
 *
 * @phpstan-import-type Extra from Generator
 * @phpstan-import-type ParsedRoute from Pattern
 * @phpstan-import-type ParsedRoutes from Pattern
 * @phpstan-import-type NamedRoutes from Route
 */
final class Binding
{
    /**
     * @param list<string>              $methods
     * @param list<string>              $middleware
     * @param array<string, string>     $wheres
     * @param array<string, string|int|bool|float> $attributes
     */
    public function __construct(
        private Route $router,
        private array $methods,
        private string $uri,
        private mixed $action,
        private string $name_prefix = '',
        private string $domain = '',
        private array $middleware = [],
        private array $wheres = [],
        private array $attributes = [],
        private string $name = '',
    ) {
    }

    public function name(string $name): self
    {
        $this->name = $name;
        $this->router->invalidate();

        return $this;
    }

    /**
     * @param  string|array<string, string>  $parameter
     */
    public function where(string|array $parameter, ?string $pattern = null): self
    {
        if (\is_array($parameter)) {
            foreach ($parameter as $name => $regex) {
                $this->wheres[$name] = $regex;
            }
        } elseif ($pattern !== null) {
            $this->wheres[$parameter] = $pattern;
        }
        $this->router->invalidate();

        return $this;
    }

    public function where_number(string $parameter): self
    {
        return $this->where($parameter, '[0-9]+');
    }

    /**
     * @param  string|list<string>  $middleware
     */
    public function middleware(string|array $middleware): self
    {
        // ponytail: recorded on extra, not executed — upgrade when an auth pipeline exists
        $this->middleware = \array_values(\array_unique(\array_merge(
            $this->middleware,
            PendingGroup::normalize_middleware($middleware),
        )));
        $this->router->invalidate();

        return $this;
    }

    public function domain(string $domain): self
    {
        $this->domain = $domain;
        $this->router->invalidate();

        return $this;
    }

    public function panel(string $panel): self
    {
        return $this->attribute(Route::PANEL, $panel);
    }

    public function cluster(string $cluster): self
    {
        return $this->attribute(Route::CLUSTER, $cluster);
    }

    public function resource(string $resource): self
    {
        return $this->attribute(Route::RESOURCE, $resource);
    }

    public function page(string $page): self
    {
        return $this->attribute(Route::PAGE, $page);
    }

    public function permission(string $permission): self
    {
        return $this->attribute(Route::PERMISSION, $permission);
    }

    public function as_view(string $view): self
    {
        return $this->attribute(Route::VIEW, $view);
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function domain_pattern(): string
    {
        return $this->domain;
    }

    /** @return list<string> */
    public function methods(): array
    {
        return $this->methods;
    }

    public function action(): mixed
    {
        return $this->action;
    }

    public function is_closure(): bool
    {
        return $this->action instanceof \Closure;
    }

    public function resolved_name(): string
    {
        if ($this->name === '') {
            return '';
        }

        return $this->name_prefix.$this->name;
    }

    public function action_label(): string
    {
        if (isset($this->attributes[Route::VIEW]) && \is_string($this->attributes[Route::VIEW]) && $this->attributes[Route::VIEW] !== '') {
            return 'view:'.$this->attributes[Route::VIEW];
        }
        if (\is_array($this->action) && isset($this->action[0], $this->action[1]) && \is_string($this->action[0]) && \is_string($this->action[1])) {
            return $this->action[0].'@'.$this->action[1];
        }
        if (\is_string($this->action) && $this->action !== '') {
            return $this->action;
        }
        if (\is_object($this->action)) {
            return $this->action::class;
        }

        return 'Closure';
    }

    public function matches_host(string $host): bool
    {
        if ($this->domain === '') {
            return true;
        }
        if ($host === '') {
            return false;
        }
        if (! \str_contains($this->domain, '{')) {
            return \strcasecmp($this->domain, $host) === 0;
        }

        return \preg_match(self::domain_regex($this->domain), $host) === 1;
    }

    public function compile(Generator $generator): void
    {
        $extra = $this->extra();
        foreach ($this->methods as $method) {
            foreach ($this->parsed() as $parsed_route) {
                if ($parsed_route === ['']) {
                    $parsed_route = ['/'];
                }
                $generator->add_route($method, $parsed_route, $this->action, $extra);
            }
        }
    }

    /**
     * @param NamedRoutes $named
     */
    public function register_named(array &$named): void
    {
        $name = $this->resolved_name();
        if ($name === '') {
            return;
        }
        if (\array_key_exists($name, $named)) {
            throw BadRoute::named_route_already_defined($name);
        }

        $named[$name] = \array_reverse($this->parsed());
    }

    /**
     * @return Extra
     */
    public function extra(): array
    {
        $extra = [Route::REGEX => $this->uri] + $this->attributes;
        $name = $this->resolved_name();
        if ($name !== '') {
            $extra[Route::NAME] = $name;
        }
        if ($this->domain !== '') {
            $extra[Route::DOMAIN] = $this->domain;
        }
        if ($this->middleware !== []) {
            $extra[Route::MIDDLEWARE] = \implode('|', $this->middleware);
        }

        return $extra;
    }

    /**
     * @return ParsedRoutes
     */
    public function parsed(): array
    {
        $parsed = (new Pattern())->parse(self::expand_optional($this->uri));
        if ($this->wheres === []) {
            return $parsed;
        }
        foreach ($parsed as $i => $parts) {
            foreach ($parts as $j => $part) {
                if (\is_array($part) && isset($this->wheres[$part[0]])) {
                    $parsed[$i][$j][1] = $this->wheres[$part[0]];
                }
            }
        }

        return $parsed;
    }

    public static function domain_regex(string $domain): string
    {
        $pattern = \preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}|[^{]+/',
            static function (array $m): string {
                if (isset($m[1]) && $m[1] !== '') {
                    return '(?P<'.$m[1].'>[^./]+)';
                }

                return \preg_quote($m[0], '`');
            },
            $domain,
        );
        \assert(\is_string($pattern) && $pattern !== '');

        return '`^'.$pattern.'$`i';
    }

    /**
     * @param Extra $extra
     *
     * @return array<string, string>
     */
    public static function domain_variables(array $extra, string $host): array
    {
        $domain = $extra[Route::DOMAIN] ?? null;
        if (! \is_string($domain) || $domain === '' || $host === '' || ! \str_contains($domain, '{')) {
            return [];
        }
        if (\preg_match(self::domain_regex($domain), $host, $matches) !== 1) {
            return [];
        }
        $vars = [];
        foreach ($matches as $key => $value) {
            if (\is_string($key)) {
                $vars[$key] = $value;
            }
        }

        return $vars;
    }

    private function attribute(string $key, string $value): self
    {
        $this->attributes[$key] = $value;
        $this->router->invalidate();

        return $this;
    }

    private static function expand_optional(string $uri): string
    {
        $suffix = '';
        while (\preg_match('~(/?)\{([a-zA-Z_][a-zA-Z0-9_-]*)\?\}$~', $uri, $m) === 1) {
            $uri = \substr($uri, 0, -\strlen($m[0]));
            $suffix = '['.$m[1].'{'.$m[2].'}'.$suffix.']';
        }

        return $uri.$suffix;
    }
}
