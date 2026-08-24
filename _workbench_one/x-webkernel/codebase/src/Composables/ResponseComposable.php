<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Psr\Http\Message\ResponseInterface;
use Webkernel\Http\Psr\Response;

final class ResponseComposable implements ComposableContract
{
    public static function api_name(): string
    {
        return 'response';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function json(array $data, int $status = 200): ResponseInterface
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return new Response($status, ['Content-Type' => 'application/json; charset=utf-8'], $json);
    }

    public function html(string $content, int $status = 200): ResponseInterface
    {
        return new Response($status, ['Content-Type' => 'text/html; charset=utf-8'], $content);
    }

    public function redirect(string $url, int $status = 302): ResponseInterface
    {
        return new Response($status, ['Location' => $url], '');
    }

    public function no_content(): ResponseInterface
    {
        return new Response(204);
    }
}
