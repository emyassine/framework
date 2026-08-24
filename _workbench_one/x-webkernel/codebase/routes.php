<?php declare(strict_types=1);

use Webkernel\Route\Route;



Route::get('/healthz', static fn (): string => 'OK');
Route::get('/ready', static fn (): string => 'OK');
Route::get('/api', static fn (): string => json_encode(['status' => 'ok', 'version' => '1.0']));
Route::get('/api/v1', static fn (): string => json_encode(['version' => '1.0']));
Route::get('/api/posts', static fn (): string => json_encode(['posts' => []]));
Route::get('/rss', static fn (): string => 'RSS Feed');
Route::get('/atom', static fn (): string => 'Atom Feed');
Route::get('/llm.txt', static fn (): string => 'LLM Content');


Route::get('/', static function (): string {
    $elapsed = number_format((hrtime(true) - START_REQUEST) / 1e6, 2) . ' ms';
    $file = __FILE__;

    // Build a long fake dataset
    $rows = '';
    $number_of_rows = 100;
    for ($i = 1; $i <= $number_of_rows; $i++) {
        $rows .= '<tr>'
            . '<td>' . $i . '</td>'
            . '<td>User_' . $i . '</td>'
            . '<td>user' . $i . '@example.com</td>'
            . '<td>' . rand(18, 65) . '</td>'
            . '<td>' . (rand(0, 1) ? 'Active' : 'Inactive') . '</td>'
            . '</tr>';
    }

    return
        '<!DOCTYPE html>' .
        '<html lang="en">' .
        '<head>' .
        '    <meta charset="UTF-8">' .
        '    <title>Webkernel</title>' .
        '    <style>' .
        '        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }' .
        '        h1 { color: #333; }' .
        '        table { border-collapse: collapse; width: 100%; margin-top: 20px; }' .
        '        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }' .
        '        th { background-color: #eee; }' .
        '        tr:nth-child(even) { background-color: #f2f2f2; }' .
        '        .footer { margin-top: 30px; font-size: 0.9em; color: #666; }' .
        '    </style>' .
        '</head>' .
        '<body>' .
        '    <h1>Welcome to Webkernel</h1>' .
        '    <p>This HTML page is served directly by a closure.</p>' .
        '    <p>Response time: ' . $elapsed . ' for ' . $number_of_rows . ' rows</p>' .
        '    <p>Served from file: ' . $file . '</p>' .
        '    <table>' .
        '        <thead>' .
        '            <tr>' .
        '                <th>ID</th>' .
        '                <th>Name</th>' .
        '                <th>Email</th>' .
        '                <th>Age</th>' .
        '                <th>Status</th>' .
        '            </tr>' .
        '        </thead>' .
        '        <tbody>' .
                     $rows .
        '        </tbody>' .
        '    </table>' .
        '    <div class="footer">Generated dynamically with fake data for demonstration purposes.</div>' .
        '</body>' .
        '</html>';
});
