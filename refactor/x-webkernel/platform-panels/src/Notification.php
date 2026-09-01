<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

use Webkernel\View\View;

/**
 * Session flash notification. Same structure as Filament's toast stack.
 */
final class Notification
{
    public const BAG = '_w_notifications';

    private string $id = '';

    private string $title = '';

    private string $body = '';

    private string $status = 'info';

    private string $icon = '';

    private int|string $duration = 6000;

    /**
     * @param $id string|null
     *
     * @return self
     */
    public static function make(?string $id = null): self
    {
        $self = new self();
        $self->id = $id ?? \bin2hex(\random_bytes(8));

        return $self;
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
     * @param $body string
     *
     * @return self
     */
    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param $icon string
     *
     * @return self
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param $duration int|string
     *
     * @return self
     */
    public function duration(int|string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @param $seconds float
     *
     * @return self
     */
    public function seconds(float $seconds): self
    {
        return $this->duration((int) ($seconds * 1000));
    }

    /**
     * @return self
     */
    public function persistent(): self
    {
        return $this->duration('persistent');
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
     * @return self
     */
    public function warning(): self
    {
        $this->status = 'warning';

        return $this;
    }

    /**
     * @return self
     */
    public function info(): self
    {
        $this->status = 'info';

        return $this;
    }

    /**
     * @return self
     */
    public function send(): self
    {
        self::boot_session();
        if (! isset($_SESSION[self::BAG]) || ! \is_array($_SESSION[self::BAG])) {
            $_SESSION[self::BAG] = [];
        }
        $_SESSION[self::BAG][] = [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'status' => $this->status,
            'icon' => $this->icon,
            'duration' => $this->duration,
        ];

        return $this;
    }

    /**
     * @return list<array{id: string, title: string, body: string, status: string, icon: string, duration: int|string}>
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
                'id' => (string) ($row['id'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'body' => (string) ($row['body'] ?? ''),
                'status' => (string) ($row['status'] ?? 'info'),
                'icon' => (string) ($row['icon'] ?? ''),
                'duration' => \is_int($row['duration'] ?? null) || \is_string($row['duration'] ?? null)
                    ? $row['duration']
                    : 6000,
            ];
        }

        return $out;
    }

    /**
     * @return string
     */
    public static function render(): string
    {
        $items = self::pull();
        if ($items === []) {
            return '';
        }
        $slot = '';
        foreach ($items as $item) {
            $slot .= View::make('webkernel::notification', $item)->render();
        }

        return View::make('webkernel::notification', ['slot' => $slot])->render();
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
