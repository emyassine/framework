# webkernel/views

Blade templates for one host application. The compiler is BladeOne (Jorge Patricio Castro Castillo, MIT) owned here — not required as a Composer package, not wrapped.

```php
Route::get('/', function () {
    return view('greeting', ['name' => 'Finn']);
});
```

```blade
Hello, {{ $name }}.
```

Templates live in `resources/views` (`greeting.blade.php`). Compiled PHP is written to `storage/framework/views` and reused until the template changes.
