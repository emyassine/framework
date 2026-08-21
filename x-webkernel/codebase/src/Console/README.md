# webkernel/console

Tempest-style CLI plus Laravel Prompts-shaped input. No Symfony Console, no `$signature` string, no `Command` parent.

```php
use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;

final readonly class Make
{
    public function __construct(
        private UserStore $store,
    ) {
    }

    #[ConsoleCommand]
    public function user(string $email, string $password, bool $admin = false): ExitCode
    {
        $name = webterminal()->text('Name', required: true);
        if (! webterminal()->confirm('Create '.$email.'?', default: true)) {
            return ExitCode::ERROR;
        }
        $this->store->add($email, $password, $admin, $name);

        return ExitCode::SUCCESS;
    }
}
```

Host binary:

```php
exit($webapp->handle_command(new ArgvInput));
```

`Make::user` is `make:user`. Method parameters are the definition: required scalars are arguments, defaults become `--options`, `bool` is a flag. Constructor is free for dependency injection.

Prompts live on the `terminal` composable:

```php
webterminal()->text('What is your name?', placeholder: 'E.g. Yassine', required: true);
webterminal()->select('Role', ['member' => 'Member', 'owner' => 'Owner']);
webterminal()->confirm('Continue?', default: false);
```

`webterminal()->fake([...])` queues answers for checks. Non-TTY falls back to fgets / defaults. Validation is a closure returning `?string` — no Laravel validator arrays.

Commands with `#[ConsoleCommand]` are listed at `composer dump-autoload` (`webkernel_commands.php`). Composer `post-autoload-dump` runs `DumpAutoloadCommand` and prints through `Terminal`. `php webkernel dump-autoload` shells out to Composer. No request-path glob.

// ponytail: form / spin / progress / table / task / stream are not built — add when a command needs a spinner or a multi-step form.
