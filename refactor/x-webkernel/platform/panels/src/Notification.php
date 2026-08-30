<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

use Webkernel\View\View;

/**
 * Session flash toast. Not a database notification.
 */
final class Notification
{
    public const BAG = '_w_notifications';

    private string $title = '';

    private string $status = 'info';

    /**
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * @param $title string
     *
     * @return self
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return self
     */
    public function success(): self
    {
        $this->status = 'success';

        return $this;
    }

    /**
     * @return self
     */
    public function danger(): self
    {
        $this->status = 'danger';

        return $this;
    }

    /**
     * @return void
     */
    public function send(): void
    {
        self::boot_session();
        if (! isset($_SESSION[self::BAG]) || ! \is_array($_SESSION[self::BAG])) {
            $_SESSION[self::BAG] = [];
        }
        $_SESSION[self::BAG][] = ['title' => $this->title, 'status' => $this->status];
    }

    /**
     * @return list<array{title: string, status: string}>
     */
    public static function pull(): array
    {
        self::boot_session();
        $raw = $_SESSION[self::BAG] ?? [];
        unset($_SESSION[self::BAG]);
        if (! \is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $row) {
            if (! \is_array($row)) {
                continue;
            }
            $out[] = [
                'title' => (string) ($row['title'] ?? ''),
                'status' => (string) ($row['status'] ?? 'info'),
            ];
        }

        return $out;
    }

    /**
     * @return string
     */
    public static function render(): string
    {
        $html = '';
        foreach (self::pull() as $item) {
            $html .= View::make('webkernel::toast', $item)->render();
        }

        return $html;
    }

    /**
     * @return void
     */
    private static function boot_session(): void
    {
        if (\class_exists(\Webkernel\Auth\Session::class, true)) {
            \Webkernel\Auth\Session::start();

            return;
        }
        if (! isset($_SESSION) || ! \is_array($_SESSION)) {
            $_SESSION = [];
        }
    }
}
