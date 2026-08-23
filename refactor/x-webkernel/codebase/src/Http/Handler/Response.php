<?php declare(strict_types=1);

namespace Webkernel\Http\Handler;

/**
 * Basic HTTP response implementation.
 */
final class Response implements ResponseInterface
{
    private int $status_code;

    /** @var array<string, string> */
    private array $headers;

    private string $body;

    /**
     * Create a new response.
     */
    public function __construct(
        int $status_code = 200,
        array $headers = [],
        string $body = ''
    ) {
        $this->status_code = $status_code;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Emit the response to the client.
     */
    public function emit(): void
    {
        // Set status code
        http_response_code($this->status_code);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Output body
        echo $this->body;
    }

    /**
     * Create a JSON response.
     */
    public static function json(
        mixed $data,
        int $status_code = 200,
        array $headers = []
    ): self {
        $headers['Content-Type'] = 'application/json';
        $body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return new self($status_code, $headers, $body);
    }

    /**
     * Create a plain text response.
     */
    public static function text(
        string $text,
        int $status_code = 200,
        array $headers = []
    ): self {
        $headers['Content-Type'] = 'text/plain';
        return new self($status_code, $headers, $text);
    }

    /**
     * Create an HTML response.
     */
    public static function html(
        string $html,
        int $status_code = 200,
        array $headers = []
    ): self {
        $headers['Content-Type'] = 'text/html; charset=UTF-8';
        return new self($status_code, $headers, $html);
    }

    /**
     * Create a 404 Not Found response.
     */
    public static function not_found(string $message = 'Not Found'): self
    {
        return new self(404, [], $message);
    }
}
