<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Errors;

final readonly class ErrorDefinition
{
    public function __construct(
        public int $code,
        public string $phrase,
        public string $icon,
        public string $title,
        public string $description,
        public string $accent,
    ) {
    }

    /**
     * @return array{code: int, phrase: string, icon: string, title: string, description: string, accent: string}
     */
    public function to_array(): array
    {
        return [
            'code' => $this->code,
            'phrase' => $this->phrase,
            'icon' => $this->icon,
            'title' => $this->title,
            'description' => $this->description,
            'accent' => $this->accent,
        ];
    }
}
