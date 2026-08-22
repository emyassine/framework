<?php declare(strict_types=1);

namespace Webkernel\Http\Psr;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

final class UploadedFile implements UploadedFileInterface
{
    private bool $moved = false;

    public function __construct(
        private readonly string $tmp_name,
        private readonly ?int $size,
        private readonly int $error,
        private readonly ?string $client_filename = null,
        private readonly ?string $client_media_type = null,
    ) {
    }

    public function getStream(): StreamInterface
    {
        if ($this->moved || $this->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Uploaded file is not available.');
        }
        $contents = (string) file_get_contents($this->tmp_name);

        return new Stream($contents);
    }

    public function moveTo(string $targetPath): void
    {
        if ($this->moved || $this->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Uploaded file is not available.');
        }
        if (! move_uploaded_file($this->tmp_name, $targetPath) && ! rename($this->tmp_name, $targetPath)) {
            throw new \RuntimeException('Unable to move uploaded file to '.$targetPath);
        }
        $this->moved = true;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->client_filename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->client_media_type;
    }
}
