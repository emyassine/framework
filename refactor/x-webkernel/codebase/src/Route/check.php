<?php declare(strict_types=1);

require dirname(__DIR__, 4).'/platform/bootstrap/fast-boot.php';

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

final class DashboardController
{
    public function show(string $tenant_id = ''): string
    {
        return 'tenant='.$tenant_id;
    }
}

Route::flush();
http_response_code(200);

Route::view('/', 'dashboard', [
    'title' => 'Webkernel — Dashboard',
])->name('dashboard');

expect(Route::url('dashboard') === '/', 'named view uri');
$listed = Route::list();
expect($listed !== [] && $listed[0]['name'] === 'dashboard', 'list name');
expect($listed[0]['action'] === 'view:dashboard', 'list action');

Route::flush();
http_response_code(200);

Route::prefix('v1')->name('admin.')->group(function (): void {
    Route::get('/dashboard/{tenant_id?}', [DashboardController::class, 'show'])
        ->where_number('tenant_id')
        ->name('dashboard');

    Route::view('/overview', 'dashboard', [
        'title' => 'Webkernel — Dashboard Overview',
    ])->name('overview');
});

expect(Route::url('admin.overview') === '/v1/overview', 'group view uri');
expect(Route::url('admin.dashboard') === '/v1/dashboard', 'optional omitted');
expect(Route::url('admin.dashboard', ['tenant_id' => '42']) === '/v1/dashboard/42', 'optional present');

$mismatch = false;
try {
    Route::url('admin.dashboard', ['tenant_id' => 'ab']);
} catch (\Webkernel\Route\Uri\UriException $e) {
    $mismatch = true;
}
expect($mismatch, 'where_number rejects url');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/v1/dashboard/9';
http_response_code(200);
expect(Route::dispatch() === 'tenant=9', 'where_number match');

$_SERVER['REQUEST_URI'] = '/v1/dashboard/ab';
http_response_code(200);
expect(Route::dispatch() === '', 'where_number 404');
expect(http_response_code() === 404, 'where_number status');

$_SERVER['REQUEST_URI'] = '/v1/dashboard';
http_response_code(200);
expect(Route::dispatch() === 'tenant=', 'optional absent');

Route::flush();
http_response_code(200);

Route::prefix('api/v1')->name('api.v1.')->get('/dashboard', static fn (): string => 'api')->name('dashboard');
expect(Route::url('api.v1.dashboard') === '/api/v1/dashboard', 'registrar verb');
$_SERVER['REQUEST_URI'] = '/api/v1/dashboard';
http_response_code(200);
expect(Route::dispatch() === 'api', 'registrar dispatch');

Route::flush();
http_response_code(200);

Route::domain('{account}.webkernel.io')
    ->get('/dash', static fn (string $account): string => 'host='.$account)
    ->name('tenant.dash');

$_SERVER['HTTP_HOST'] = 'acme.webkernel.io';
$_SERVER['REQUEST_URI'] = '/dash';
http_response_code(200);
expect(Route::dispatch() === 'host=acme', 'domain match');

$_SERVER['HTTP_HOST'] = 'localhost';
http_response_code(200);
expect(Route::dispatch() === '', 'domain miss');
expect(http_response_code() === 404, 'domain miss status');

echo "ok\n";
