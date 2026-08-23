<?php declare(strict_types=1);

namespace Webkernel\Router;

/**
 * Représente une route unique
 * Ultra-léger pour les performances
 */
final class Route
{
    private string $method;
    private string $path;
    private callable|string|array $action;
    private array $middleware;
    private ?string $name = null;

    /** @var array<string, mixed> */
    private array $where = [];

    public function __construct(
        string $method,
        string $path,
        callable|string|array $action,
        array $middleware = []
    ) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->action = $action;
        $this->middleware = $middleware;
    }

    /**
     * Définir un nom pour la route
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Ajouter des contraintes de validation
     */
    public function where(string $parameter, string $constraint): self
    {
        $this->where[$parameter] = $constraint;
        return $this;
    }

    /**
     * Ajouter un middleware
     */
    public function with_middleware(string|array $middleware): self
    {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } else {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
        return $this;
    }

    // Getters

    public function get_method(): string
    {
        return $this->method;
    }

    public function get_path(): string
    {
        return $this->path;
    }

    public function get_action(): callable|string|array
    {
        return $this->action;
    }

    public function get_middleware(): array
    {
        return $this->middleware;
    }

    public function get_name(): ?string
    {
        return $this->name;
    }

    public function get_where(): array
    {
        return $this->where;
    }

    /**
     * Vérifier si la route a un nom
     */
    public function has_name(): bool
    {
        return $this->name !== null;
    }
}
