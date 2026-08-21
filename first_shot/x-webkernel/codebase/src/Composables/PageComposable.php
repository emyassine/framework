<?php declare(strict_types=1);

namespace Webkernel\Composables;

final class PageComposable implements ComposableContract
{
    /** @var list<string> */
    private array $components = [];

    private string $template = '';

    public static function api_name(): string
    {
        return 'page';
    }

    public static function container_lifetime(): string
    {
        return 'scoped';
    }

    /**
     * @return list<string>
     */
    public function components(): array
    {
        return $this->components;
    }

    public function render(): string
    {
        if ($this->template === '') {
            return '';
        }

        return webapp()->view()->render($this->template);
    }
}
