<?php declare(strict_types=1);

namespace Webkernel\Route\Action;

use Webkernel\View\View;

/**
 * Serializable Route::view() handler. Closures cannot be dumped.
 */
final readonly class ViewAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $view,
        public array $data = [],
        public int $status = 200,
    ) {
    }

    public function __invoke(): View
    {
        if ($this->status !== 200) {
            http_response_code($this->status);
        }

        return View::make($this->view, $this->data);
    }

    /**
     * @param array{view: string, data: array<string, mixed>, status: int} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['view'], $properties['data'], $properties['status']);
    }
}
