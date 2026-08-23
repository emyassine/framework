<?php declare(strict_types=1);

namespace Webkernel\Router;

/**
 * Router principal de Webkernel - version ultra-rapide
 * Optimisé pour la performance avec lookup en O(1)
 */
final class Router
{
    /** @var array<string, Route> Routes par method:path */
    private array $routes = [];

    /** @var array<string, array<string, Route>> Routes groupés par méthode */
    private array $method_routes = [];

    /** @var array<string, Route> Routes par nom */
    private array $named_routes = [];

    /** @var array<string, mixed> Attributs actuels du groupe */
    private array $group_stack = [];

    /**
     * Ajouter une route
     */
    public function add(string $method, string $path, callable|string|array $action, array $middleware = []): Route
    {
        $normalized_path = $this->normalize_path($path);
        $method = strtoupper($method);

        // Appliquer les attributs du groupe si présent
        $current_group = end($this->group_stack) ?: [];
        if (isset($current_group['prefix'])) {
            $normalized_path = $this->normalize_path($current_group['prefix'] . $normalized_path);
        }
        if (isset($current_group['middleware'])) {
            $middleware = array_merge($current_group['middleware'], $middleware);
        }

        $route = new Route($method, $normalized_path, $action, $middleware);

        $key = $method . ':' . $normalized_path;
        $this->routes[$key] = $route;
        $this->method_routes[$method][$normalized_path] = $route;

        // Enregistrer par nom si présent
        if ($route->has_name()) {
            $this->named_routes[$route->get_name()] = $route;
        }

        return $route;
    }

    /**
     * Créer un groupe de routes avec préfixe/middleware partagé
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->group_stack[] = $attributes;
        $callback($this);
        array_pop($this->group_stack);
    }

    /**
     * Normaliser le chemin pour stockage cohérent
     */
    private function normalize_path(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/';
        }
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        // Supprimer les slashes multiples
        $path = preg_replace('#/+#', '/', $path);
        return rtrim($path, '/') ?: '/';
    }

    /**
     * Trouver une route correspondante - SUPER RAPIDE
     */
    public function match(string $path, string $method = 'GET'): ?Route
    {
        $normalized_path = $this->normalize_path($path);
        $method = strtoupper($method);

        // Lookup direct en O(1)
        if (isset($this->method_routes[$method][$normalized_path])) {
            return $this->method_routes[$method][$normalized_path];
        }

        // TODO: Supporter les paramètres dynamiques comme /posts/{id}
        // Pour l'instant, seulement les correspondances exactes

        return null;
    }

    /**
     * Obtenir une route par son nom
     */
    public function get_by_name(string $name): ?Route
    {
        return $this->named_routes[$name] ?? null;
    }

    /**
     * Obtenir toutes les routes
     */
    public function get_routes(): array
    {
        return $this->routes;
    }

    /**
     * Générer une carte plate pour la compilation
     * Format: method:path => [handler, middleware]
     */
    public function flat_map(): array
    {
        $map = [];
        foreach ($this->routes as $key => $route) {
            $map[$key] = [
                'method' => $route->get_method(),
                'path' => $route->get_path(),
                'handler' => $route->get_action(),
                'middleware' => $route->get_middleware(),
                'name' => $route->get_name(),
            ];
        }
        return $map;
    }

    /**
     * Nettoyer le router (utile pour les tests)
     */
    public function reset(): void
    {
        $this->routes = [];
        $this->method_routes = [];
        $this->named_routes = [];
    }
}
