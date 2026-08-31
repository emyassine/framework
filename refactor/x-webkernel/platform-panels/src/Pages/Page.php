<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Pages;

use Webkernel\Liveview\Liveview;
use Webkernel\Platform\Actions\Action;
use Webkernel\Platform\Notification;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\View\View;

/**
 * Panel page. Title, subheader, breadcrumbs and header actions come from here.
 *
 * //> Views are the body only. The page frame is <x-webkernel::page> (rail, sidebar, topbar, main, aside).
 * //> can_access() is per page. Panel roles/permissions wire this later.
 */
abstract class Page
{
    public const HEADER = '';

    public const SUBHEADER = '';

    protected static string $slug = '';

    /**
     * @return string
     */
    public static function get_slug(): string
    {
        if (static::$slug !== '') {
            return static::$slug;
        }
        $short = (new \ReflectionClass(static::class))->getShortName();
        $kebab = \preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $short);

        return \strtolower(\is_string($kebab) ? $kebab : $short);
    }

    /**
     * @param $path string
     * @param $methods list<string>
     *
     * @return array{class: class-string, path: string, methods: list<string>}
     */
    public static function route(string $path = '', array $methods = ['GET']): array
    {
        if ($path === '') {
            $path = '/'.static::get_slug();
        }

        return ['class' => static::class, 'path' => $path, 'methods' => $methods];
    }

    /**
     * @return bool
     */
    public static function can_access(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public static function get_navigation_label(): string
    {
        if (static::HEADER !== '') {
            return static::HEADER;
        }

        return self::label_from_class(static::class);
    }

    /**
     * @return string
     */
    public function get_title(): string
    {
        return $this->get_header();
    }

    /**
     * @return string
     */
    public function get_header(): string
    {
        if (static::HEADER !== '') {
            return static::HEADER;
        }

        return self::label_from_class(static::class);
    }

    /**
     * @return string
     */
    public function get_subheader(): string
    {
        return static::SUBHEADER;
    }

    /**
     * @return list<Action|string>
     */
    public function get_header_actions(): array
    {
        return [];
    }

    /**
     * @return list<Action>
     */
    public function get_footer_actions(): array
    {
        return [];
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    public function get_breadcrumbs(): array
    {
        $out = [];
        $panel = \function_exists('webapp') ? \webapp()->panel()->matching_path() : null;
        if (\is_array($panel)) {
            $label = (string) ($panel['label'] ?? '');
            $href = (string) ($panel['home_url'] ?? $panel['href'] ?? '');
            if ($label !== '') {
                $out[] = ['label' => $label, 'href' => $href];
            }
        }
        $header = $this->get_header();
        if ($header !== '') {
            $out[] = ['label' => $header, 'href' => ''];
        }

        return $out;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    public function view_data(): array
    {
        return [];
    }

    /**
     * @param $arguments mixed
     *
     * @return string
     */
    public function __invoke(mixed ...$arguments): string
    {
        return $this->render();
    }

    /**
     * @param $slot string|null
     *
     * @return string
     */
    public function render(?string $slot = null): string
    {
        if (! static::can_access()) {
            \http_response_code(403);
            return 'Forbidden';
        }
        $body = $slot;
        $data = $body === null ? $this->view_data() : [];
        if ($body === null) {
            $view = $this->view();
            $body = $view !== '' ? View::make($view, $data)->html() : $this->render_schema($data);
        }
        $body = Notification::render().$body;
        if (Liveview::is_request()) {
            return $body;
        }
        $header_actions = [];
        foreach ($this->get_header_actions() as $action) {
            $header_actions[] = $action instanceof Action ? $action->render() : (string) $action;
        }

        return View::make('webkernel::page', [
            'title' => $this->get_title(),
            'header' => $this->get_header(),
            'description' => $this->get_subheader(),
            'header_actions' => $header_actions,
            'breadcrumbs' => $this->get_breadcrumbs(),
            'slot' => $body,
        ])->render();
    }

    /**
     * @param $class class-string
     *
     * @return string
     */
    public static function label_from_class(string $class): string
    {
        $short = (new \ReflectionClass($class))->getShortName();
        foreach (['Resource', 'Page'] as $suffix) {
            if (\str_ends_with($short, $suffix) && $short !== $suffix) {
                $short = \substr($short, 0, -\strlen($suffix));
            }
        }
        $spaced = \preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $short);

        return \is_string($spaced) ? $spaced : $short;
    }

    /**
     * @param $data array<string, mixed>
     *
     * @return string
     */
    private function render_schema(array $data): string
    {
        $schema = $data['schema'] ?? null;
        if ($schema instanceof Schema) {
            return $schema->to_html();
        }

        return '';
    }
}
