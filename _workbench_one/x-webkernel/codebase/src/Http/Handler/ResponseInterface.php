<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

/**
 * Interface for handler responses.
 * All handlers must return a ResponseInterface implementation.
 */
interface ResponseInterface
{
    /**
     * Emit the response to the client.
     */
    public function emit(): void;
}
