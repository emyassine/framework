# webkernel/route

HTTP router for one host application. The MarkBased engine from FastRoute (Nikita Popov, BSD-3-Clause) is owned here — not required as a Composer package, not wrapped.

```php
Route::get('/', function () {
    return view('greeting', ['name' => 'Finn']);
});

Route::get('/invoices/{id}', InvoicePage::class, [
    Route::NAME => 'invoices.show',
    Route::PANEL => 'accounting',
    Route::CLUSTER => 'sales',
    Route::RESOURCE => 'invoice',
    Route::PAGE => 'show',
    Route::PERMISSION => 'invoice.view',
]);

echo Route::dispatch();
```

One dispatcher strategy (MarkBased). Extra keys name the panel / cluster / resource / page / permission the URI belongs to. Permission is recorded, not enforced, until the platform auth layer exists.
