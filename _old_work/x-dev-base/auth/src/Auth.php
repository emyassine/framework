<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth;

use Webkernel\Composables\ComposableContract;
use Webkernel\Config\Config;

/**
 * Session guard. `webapp()->auth()`.
 *
 * //> acting_as() is the test door. It does not write the session.
 */
final class Auth implements ComposableContract
{
    private static ?User $resolved = null;

    private static bool $loaded = false;

    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'auth';
    }

    /**
     * @return self
     */
    public static function get(): self
    {
        return new self();
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$resolved = null;
        self::$loaded = false;
    }

    /**
     * @return void
     */
    public static function boot(): void
    {
        Session::start();
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * @return User|null
     */
    public function user(): ?User
    {
        if (self::$loaded) {
            return self::$resolved;
        }
        self::$loaded = true;
        Session::start();
        $id = Session::get(Session::AUTH_ID);
        if ($id === null || $id === '') {
            return self::$resolved = null;
        }
        try {
            self::$resolved = $this->user_class()::find(\is_int($id) ? $id : (string) $id);
        } catch (\Throwable) {
            self::$resolved = null;
        }

        return self::$resolved;
    }

    /**
     * @return int|string|null
     */
    public function id(): int|string|null
    {
        $user = $this->user();

        return $user?->get_auth_identifier();
    }

    /**
     * @param $email string
     * @param $password string
     *
     * @return bool
     */
    public function attempt(string $email, string $password): bool
    {
        try {
            $row = $this->user_class()::query()->where('email', $email)->first();
        } catch (\Throwable) {
            return false;
        }
        if ($row === null) {
            return false;
        }
        $user = new ($this->user_class())($row);
        $hash = (string) ($row['password'] ?? '');
        if (! Hash::check($password, $hash)) {
            return false;
        }
        $this->login($user);

        return true;
    }

    /**
     * @param $user User
     *
     * @return void
     */
    public function login(User $user): void
    {
        Session::put(Session::AUTH_ID, $user->get_auth_identifier());
        self::$resolved = $user;
        self::$loaded = true;
    }

    /**
     * Test door. Does not persist the session.
     *
     * @param $user User
     *
     * @return void
     */
    public function acting_as(User $user): void
    {
        self::$resolved = $user;
        self::$loaded = true;
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        Session::forget(Session::AUTH_ID);
        self::$resolved = null;
        self::$loaded = true;
    }

    /**
     * @return string
     */
    public function login_path(): string
    {
        $path = Config::get('auth.login_path', '/login');

        return \is_string($path) && $path !== '' ? $path : '/login';
    }

    /**
     * @return class-string<User>
     */
    private function user_class(): string
    {
        $class = Config::get('auth.user', User::class);

        return \is_string($class) && $class !== '' && \is_a($class, User::class, true)
            ? $class
            : User::class;
    }
}
