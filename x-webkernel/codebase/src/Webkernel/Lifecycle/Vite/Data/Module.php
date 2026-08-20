<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Data;

/**
 * One Webkernel package resolved into a Vite/Tailwind module root.
 */
final class Module
{
    public function __construct(
        public readonly string $root,
        public readonly ?string $config_file,
        public readonly bool $has_config,
        public readonly string $name,
    ) {
    }

    /**
     * @return array{root: string, config_file: ?string, has_config: bool, name: string}
     */
    public function to_array(): array
    {
        return [
            'root' => $this->root,
            'config_file' => $this->config_file,
            'has_config' => $this->has_config,
            'name' => $this->name,
        ];
    }
}
