# webkernel/route

HTTP router for one host application. The MarkBased engine from FastRoute (Nikita Popov, BSD-3-Clause) is owned here — not required as a Composer package, not wrapped.

```php
Route::get('/', function () {
    return view('greeting', ['name' => 'Finn']);
});

Route::get('/invoices/{id}', InvoicePage::class)
    ->name('invoices.show')
    ->whereNumber('id')
    ->panel('accounting')
    ->cluster('sales')
    ->resource('invoice')
    ->page('show')
    ->permission('invoice.view');

Route::prefix('v1')->name('admin.')->group(function () {
    Route::view('/overview', 'dashboard', [
        'title' => 'Webkernel — Dashboard Overview',
    ])->name('overview');
});

echo Route::dispatch();
```

One dispatcher strategy (MarkBased). Fluent modifiers name the panel / cluster / resource / page / permission the URI belongs to. Permission and middleware are recorded, not enforced, until the platform auth layer exists.
