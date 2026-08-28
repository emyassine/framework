<?php declare(strict_types=1);

namespace Webkernel\View;

/**
 * JSON for HTML / JS embedding. Laravel `Js::from()` usage.
 */
final class Js implements \Stringable
{
    private function __construct(
        private readonly string $js,
    ) {
    }

    public static function from(mixed $data, int $flags = 0, int $depth = 512): self
    {
        $json = \json_encode(
            $data,
            $flags | JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE,
            $depth,
        );

        return new self('JSON.parse(\''.\str_replace(['\\', "'"], ['\\\\', "\\'"], $json).'\')');
    }

    public function __toString(): string
    {
        return $this->js;
    }
}
