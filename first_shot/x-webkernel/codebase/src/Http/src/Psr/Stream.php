<?php declare(strict_types=1);

namespace Webkernel\Http\Psr;

use Psr\Http\Message\StreamInterface;

/**
 * String-backed PSR-7 stream. Seekable in-memory buffer.
 */
final class Stream implements StreamInterface
{
    /** @var resource|null */
    private $handle;

    private bool $seekable;

    private bool $readable;

    private bool $writable;

    public function __construct(string $contents = '')
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open php://temp.');
        }
        $this->handle = $handle;
        $this->seekable = true;
        $this->readable = true;
        $this->writable = true;
        if ($contents !== '') {
            fwrite($handle, $contents);
            rewind($handle);
        }
    }

    public function __toString(): string
    {
        if ($this->handle === null) {
            return '';
        }
        try {
            $this->seek(0);

            return (string) stream_get_contents($this->handle);
        } catch (\Throwable) {
            return '';
        }
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
        $this->handle = null;
    }

    public function detach()
    {
        $handle = $this->handle;
        $this->handle = null;

        return $handle;
    }

    public function getSize(): ?int
    {
        if ($this->handle === null) {
            return 0;
        }
        $stat = fstat($this->handle);

        return is_array($stat) ? (int) $stat['size'] : null;
    }

    public function tell(): int
    {
        if ($this->handle === null) {
            throw new \RuntimeException('Stream is detached.');
        }
        $pos = ftell($this->handle);
        if ($pos === false) {
            throw new \RuntimeException('Unable to tell stream position.');
        }

        return $pos;
    }

    public function eof(): bool
    {
        return $this->handle === null || feof($this->handle);
    }

    public function isSeekable(): bool
    {
        return $this->handle !== null && $this->seekable;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if ($this->handle === null || fseek($this->handle, $offset, $whence) !== 0) {
            throw new \RuntimeException('Unable to seek stream.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->handle !== null && $this->writable;
    }

    public function write(string $string): int
    {
        if ($this->handle === null) {
            throw new \RuntimeException('Stream is detached.');
        }
        $written = fwrite($this->handle, $string);
        if ($written === false) {
            throw new \RuntimeException('Unable to write to stream.');
        }

        return $written;
    }

    public function isReadable(): bool
    {
        return $this->handle !== null && $this->readable;
    }

    public function read(int $length): string
    {
        if ($this->handle === null) {
            throw new \RuntimeException('Stream is detached.');
        }
        $data = fread($this->handle, $length);
        if ($data === false) {
            throw new \RuntimeException('Unable to read stream.');
        }

        return $data;
    }

    public function getContents(): string
    {
        if ($this->handle === null) {
            throw new \RuntimeException('Stream is detached.');
        }
        $data = stream_get_contents($this->handle);
        if ($data === false) {
            throw new \RuntimeException('Unable to read stream contents.');
        }

        return $data;
    }

    public function getMetadata(?string $key = null)
    {
        if ($this->handle === null) {
            return $key === null ? [] : null;
        }
        $meta = stream_get_meta_data($this->handle);
        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}
