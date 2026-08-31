<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview;

use Webkernel\View\View;

/**
 * Base class for Liveview components (HTMX-powered reactive components).
 *
 * //> Inspired by Laravel Livewire and Yoyo. Components can handle requests,
 * //> maintain state, and return HTML that can be swapped via HTMX.
 */
abstract class Component
{
    use Concerns\HasProps;
    use Concerns\HasActions;
    use Concerns\HasLifecycleHooks;

    /**
     * The component's unique ID.
     */
    public string $id;

    /**
     * @var array<string, mixed>
     */
    protected array $query = [];

    /**
     * @var array<string, mixed>
     */
    private array $state = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = $this->generate_id();
        $this->boot();
    }

    /**
     * Generate a unique ID for the component.
     *
     * @return string
     */
    protected function generate_id(): string
    {
        return 'lw-'.bin2hex(random_bytes(8));
    }

    /**
     * Boot the component. Override this to set up initial state.
     *
     * @return void
     */
    protected function boot(): void
    {
        // Override in child classes
    }

    /**
     * The view to render for this component.
     *
     * @return string
     */
    abstract public function view(): string;

    /**
     * Render the component to HTML.
     *
     * @param array<string, mixed> $extra_data
     * @return string
     */
    public function render(array $extra_data = []): string
    {
        $data = array_merge($this->all(), $extra_data);

        return View::make($this->view(), $data)->html();
    }

    /**
     * Get all data to pass to the view.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge([
            'id' => $this->id,
        ], $this->props, $this->state);
    }

    /**
     * Handle an HTMX request for this component.
     *
     * @param string $action
     * @param array<string, mixed> $params
     * @return string
     */
    public function handle(string $action, array $params = []): string
    {
        // Call the action if it exists
        if (method_exists($this, $action)) {
            $this->$action(...$params);
        }

        // Return the rendered component
        return $this->render();
    }

    /**
     * Hydrate the component from request data.
     *
     * @param array<string, mixed> $data
     * @return static
     */
    public function hydrate(array $data): static
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * Dehydrate the component to an array.
     *
     * @return array<string, mixed>
     */
    public function dehydrate(): array
    {
        return $this->all();
    }

    /**
     * Get the component's state as JSON for HTMX.
     *
     * @return string
     */
    public function to_json(): string
    {
        return json_encode($this->dehydrate(), JSON_THROW_ON_ERROR);
    }

    /**
     * Emit an event to the frontend.
     *
     * @param string $event
     * @param mixed $data
     * @return void
     */
    public function emit(string $event, mixed $data = null): void
    {
        Liveview::response()->trigger($event, $data)->send();
    }

    /**
     * Redirect to a URL.
     *
     * @param string $url
     * @return void
     */
    public function redirect_to(string $url): void
    {
        Liveview::response()->redirect($url)->send();
    }

    /**
     * Refresh the page.
     *
     * @return void
     */
    public function refresh_page(): void
    {
        Liveview::response()->refresh()->send();
    }

    /**
     * Magic method to handle property access.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        // Check state first
        if (isset($this->state[$name])) {
            return $this->state[$name];
        }

        // Check props
        if (isset($this->props[$name])) {
            return $this->props[$name];
        }

        // Check query params
        if (isset($this->query[$name])) {
            return $this->query[$name];
        }

        throw new \RuntimeException("Property [{$name}] does not exist on component [".static::class."].");
    }

    /**
     * Magic method to handle property assignment.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        // Set on state
        $this->state[$name] = $value;
    }

    /**
     * Check if a property exists.
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->state[$name])
            || isset($this->props[$name])
            || isset($this->query[$name]);
    }
}
