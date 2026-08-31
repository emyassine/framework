<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Component;

use Webkernel\View\AttributeBag;
use Webkernel\View\View;

/**
 * Base class for all Webkernel components (both static and reactive).
 *
 * //> Provides common functionality: props, view rendering, attribute bag.
 */
abstract class Component
{
    use Concerns\HasProps;

    /**
     * Component name for blade/x tags.
     */
    protected string $name = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Boot the component. Override this for initialization.
     *
     * @return void
     */
    protected function boot(): void
    {
        // Override in child classes
    }

    /**
     * The view to render for this component.
     *
     * @return string
     */
    abstract public function view(): string;

    /**
     * Set the component name.
     *
     * @param string $name
     * @return static
     */
    public function set_name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the component name.
     *
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Render the component to HTML.
     *
     * @param array<string, mixed> $extra
     * @return string
     */
    public function render(array $extra = []): string
    {
        $data = $this->to_props();
        $data = array_merge($data, $extra);

        if (! \array_key_exists('slot', $data)) {
            $data['slot'] = '';
        }

        if (! isset($data['attributes']) || ! $data['attributes'] instanceof AttributeBag) {
            $bag = $this->extra_attribute_bag();
            if (isset($extra['class']) && \is_string($extra['class']) && $extra['class'] !== '') {
                $bag['class'] = \trim((string) ($bag['class'] ?? '').' '.$extra['class']);
            }
            $data['attributes'] = new AttributeBag($bag);
        }

        return View::make($this->view(), $data)->html();
    }

    /**
     * Get all props for the component.
     *
     * @return array<string, mixed>
     */
    public function to_props(): array
    {
        return array_merge(['name' => $this->name], $this->get_props());
    }

    /**
     * Get the component declaration for dumping.
     *
     * @return array{component: class-string, view: string, props: array<string, mixed>}
     */
    public function to_array(): array
    {
        return [
            'component' => static::class,
            'view' => $this->view(),
            'props' => $this->to_props(),
        ];
    }

    /**
     * Set a slot content.
     *
     * @param string $html
     * @return static
     */
    public function slot(string $html): static
    {
        $this->set_prop('slot', $html);
        return $this;
    }

    /**
     * Get extra attributes for the component.
     * Override this in child classes to add component-specific attributes.
     *
     * @return array<string, mixed>
     */
    protected function extra_attribute_bag(): array
    {
        return [];
    }
}
