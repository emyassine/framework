<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth\Console;

use Webkernel\Auth\Hash;
use Webkernel\Auth\User;
use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;

final readonly class UserCreateCommand
{
    /**
     * @param $email string
     * @param $name string
     * @param $password string
     *
     * @return ExitCode
     */
    #[ConsoleCommand(
        name: 'user:create',
        description: 'Create a user (--name= --password=)',
    )]
    public function __invoke(string $email, string $name = 'Admin', string $password = 'password'): ExitCode
    {
        $existing = User::query()->where('email', $email)->first();
        if ($existing !== null) {
            webterminal()->error('user already exists: '.$email);

            return ExitCode::INVALID;
        }
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'created_at' => \gmdate('c'),
            'updated_at' => \gmdate('c'),
        ]);
        webterminal()->info('created '.$email);

        return ExitCode::SUCCESS;
    }
}
