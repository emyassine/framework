<?php declare(strict_types=1);

require dirname(__DIR__, 4).'/third_party/autoload.php';

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;
use Webkernel\Console\Input\ArgvInput;
use Webkernel\WebApp;

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

final class Dep
{
}

final readonly class NeedsDep
{
    public function __construct(
        public Dep $dep,
    ) {
    }

    #[ConsoleCommand(name: 'needs-dep')]
    public function __invoke(): ExitCode
    {
        $GLOBALS['wk_dep'] = $this->dep instanceof Dep;

        return ExitCode::SUCCESS;
    }
}

final class Make
{
    public static string $hit = '';

    #[ConsoleCommand]
    public function user(string $email, string $password, bool $admin = false): ExitCode
    {
        self::$hit = $email.'|'.$password.($admin ? '|admin' : '');

        return ExitCode::SUCCESS;
    }
}

WebApp::flush();
$app = WebApp::configure()->with_routes()->create();
$app->declare('commands', [Make::class, NeedsDep::class]);

$input = new ArgvInput(['webkernel', 'make:user', 'a@b.c', 'secret', '--admin']);
expect($input->command() === 'make:user', 'argv command');
expect($input->arguments() === ['a@b.c', 'secret'], 'argv args');
expect($input->option('admin') === true, 'argv flag');

expect($app->handle_command(new ArgvInput(['webkernel', '--help'])) === 0, 'help exit');
expect($app->handle_command(new ArgvInput(['webkernel'])) === 1, 'missing command');
expect($app->handle_command(new ArgvInput(['webkernel', 'nope'])) === 2, 'unknown command');

Make::$hit = '';
expect($app->handle_command(new ArgvInput(['webkernel', 'make:user', 'a@b.c', 'secret', '--admin'])) === 0, 'make:user');
expect(Make::$hit === 'a@b.c|secret|admin', 'mapped argv');

$GLOBALS['wk_dep'] = false;
expect($app->handle_command(new ArgvInput(['webkernel', 'needs-dep'])) === 0, 'needs-dep');
expect($GLOBALS['wk_dep'] === true, 'constructor di');

expect($app->handle_command(new ArgvInput(['webkernel', 'routes:list'])) === 0, 'routes:list');
expect($app->handle_command(new ArgvInput(['webkernel', 'server', '--help'])) === 0, 'server help');
expect($app->handle_command(new ArgvInput(['webkernel', 'dump-autoload', '--help'])) === 0, 'dump-autoload help');

$t = webterminal();
expect($t === webterminal(), 'terminal singleton');

$t->fake(['Yassine']);
expect($t->text('Name') === 'Yassine', 'text fake');

$t->fake(['ab', 'abcd']);
expect($t->text('Name', validate: fn (string $v): ?string => strlen($v) < 4 ? 'short' : null) === 'abcd', 'text validate');

$t->fake(['', 'ok']);
expect($t->text('Name', required: true) === 'ok', 'text required');

$t->fake(['secret']);
expect($t->password('Password') === 'secret', 'password');

$t->fake([3]);
expect($t->number('Copies', min: 1, max: 10) === 3, 'number');

$t->fake([true]);
expect($t->confirm('Continue?') === true, 'confirm');

$t->fake(['owner']);
expect($t->select('Role', ['member' => 'Member', 'owner' => 'Owner']) === 'owner', 'select assoc');

$t->fake(['Owner']);
expect($t->select('Role', ['Member', 'Contributor', 'Owner']) === 'Owner', 'select list');

$t->fake([['read', 'write']]);
expect($t->multiselect('Perms', ['read' => 'Read', 'write' => 'Write', 'delete' => 'Delete']) === ['read', 'write'], 'multiselect');

$t->fake(['Taylor']);
expect($t->suggest('Name', ['Taylor', 'Dayle']) === 'Taylor', 'suggest');

$t->fake(['member']);
expect($t->search('Role', ['member' => 'Member', 'owner' => 'Owner']) === 'member', 'search');

$t->fake([]);
$t->pause('go');

echo "ok\n";
