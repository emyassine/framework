<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Route\Action;

/**
 * Serializable Route::redirect() handler. Closures cannot be dumped.
 */
final readonly class RedirectAction
{
    public function __construct(
        public string $destination,
        public int $status = 302,
    ) {
    }

    public function __invoke(): string
    {
        \http_response_code($this->status);
        \header('Location: '.$this->destination, true, $this->status);

        return '';
    }

    /**
     * @param array{destination: string, status: int} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['destination'], $properties['status']);
    }
}
