<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Component;

use Webkernel\Liveview\Liveview;
use Webkernel\Liveview\ResponseHelper;

/**
 * Base class for reactive Liveview components.
 *
 * //> Reactive components maintain state and handle HTMX requests.
 * //> Used for: Counter, Form, Wizard, etc.
 * //> Extends Component with state management, hydration, and HTMX helpers.
 */
abstract class ReactiveComponent extends Component
{
    use Concerns\HasActions;
    use Concerns\HasLifecycleHooks;

    /**
     * The component's unique ID.
     */
    public string $id;

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
        parent::__construct();
    }

    /**
     * Generate a unique ID for the component.
     *
     * @return string
     */
    protected function generate_id(): string
    {
        return 'liveview-'.bin2hex(random_bytes(8));
    }

    /**
     * Get all data to pass to the view.
     *
     * @return array<string, mixed>
     */
    public function to_props(): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
        ], parent::get_props(), $this->state);
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
        $this->before_action($action, $params);

        // Call the action if it exists
        if (method_exists($this, $action)) {
            $this->$action(...$params);
        }

        $this->after_action($action, $params);

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
        $this->on_hydrate($data);

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                $this->state[$key] = $value;
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
        $this->on_dehydrate();

        return $this->to_props();
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
     * Emit an event without sending (for batching).
     *
     * @param string $event
     * @param mixed $data
     * @return ResponseHelper
     */
    public function with_emit(string $event, mixed $data = null): ResponseHelper
    {
        return Liveview::response()->trigger($event, $data);
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
     * Get the ResponseHelper for building custom responses.
     *
     * @return ResponseHelper
     */
    public function response(): ResponseHelper
    {
        return Liveview::response();
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

        // Check parent props
        return parent::get_prop($name);
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
        return isset($this->state[$name]) || parent::__isset($name);
    }
}
