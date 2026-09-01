<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth;

use Webkernel\Models\Model;

/**
 * Session identity. Table `users`.
 */
class User extends Model
{
    protected string $table = 'users';

    /** @var list<string> */
    protected array $hidden = ['password'];

    /**
     * @return int|string
     */
    public function get_auth_identifier(): int|string
    {
        $id = $this->get_attribute('id');

        return \is_int($id) || \is_string($id) ? $id : (string) $id;
    }

    /**
     * @return string
     */
    public function get_auth_password(): string
    {
        return (string) $this->get_attribute('password');
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return (string) $this->get_attribute('name');
    }

    /**
     * @return string
     */
    public function get_email(): string
    {
        return (string) $this->get_attribute('email');
    }
}
