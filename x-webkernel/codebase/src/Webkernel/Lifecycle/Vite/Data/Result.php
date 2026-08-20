<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Data;

/**
 * Outcome of generate() / vite_npm_build(). Immutable, no array shape.
 */
final class Result
{
    private function __construct(
        public readonly bool $ok,
        public readonly int $exit_code,
        public readonly ?string $path,
        public readonly string $raw,
        public readonly string $stderr,
    ) {
    }

    public static function success(string $path, string $raw): self
    {
        return new self(
            ok: true,
            exit_code: 0,
            path: $path,
            raw: $raw,
            stderr: '',
        );
    }

    public static function failure(string $stderr, int $exit_code = 1): self
    {
        return new self(
            ok: false,
            exit_code: $exit_code,
            path: null,
            raw: '',
            stderr: $stderr,
        );
    }

    /**
     * @return array{ok: bool, exit_code: int, path: ?string, raw: string, stderr: string}
     */
    public function to_array(): array
    {
        return [
            'ok' => $this->ok,
            'exit_code' => $this->exit_code,
            'path' => $this->path,
            'raw' => $this->raw,
            'stderr' => $this->stderr,
        ];
    }
}
