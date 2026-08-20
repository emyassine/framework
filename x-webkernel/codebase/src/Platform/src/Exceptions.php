<?php declare(strict_types=1);

namespace Webkernel\Platform;

use Webkernel\Http\Request;

/**
 * Exception renderer configuration (Laravel-shaped).
 * Callbacks are recorded; the renderer is not wired yet.
 */
final class Exceptions
{
    /** @var (callable(Request): bool)|null */
    private $should_render_json_when = null;

    /**
     * @param callable(Request): bool $callback
     */
    public function should_render_json_when(callable $callback): self
    {
        $this->should_render_json_when = $callback;

        return $this;
    }

    public function renders_json(Request $request): bool
    {
        if ($this->should_render_json_when === null) {
            return false;
        }

        return (bool) ($this->should_render_json_when)($request);
    }
}
