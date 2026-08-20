# webkernel/views

Templates are `{name}.view.php`. The compiler is BladeOne (Jorge Patricio Castro Castillo, MIT) owned here — not required as a Composer package, not wrapped.

```php
Route::get('/', function () {
    return view('greeting', ['name' => 'Finn']);
});
```

```
Hello, {{ $name }}.
```

Templates live in `resources/views` (`greeting.view.php`) and in each package `views/` directory declared at `composer dump-autoload`. Compiled PHP is `{name}_{hash}.view.php.compiled` under `storage/framework/views`.

## Layouts

Same idea as Filament: pick a chrome, only that chrome's CSS is compiled in.

| Layout | Use | CSS |
| --- | --- | --- |
| `layouts.base` | Bare document | tokens |
| `layouts.simple` | Login, empty states | tokens + centered card |
| `layouts.page` | App chrome | tokens + sidebar/topnav/horizontal shell + components |

```
@extends('layouts.page')

@section('content')
  <h1 class="wks-page-title">Overview</h1>
@endsection
```

`data-wds-layout` on `<html>` is `sidebar` (default), `topnav`, or `horizontal` for `layouts.page`. Simple pages never load shell CSS.

## Packages

```json
"extra": {
  "webkernel": {
    "views": "views",
    "routes": "routes/web.php",
    "eager": false
  }
}
```

Missing `views` / `routes` keys still pick up `views/`, `resources/views/`, `routes/web.php`, and `routes.php` when those paths exist. `eager: true` is only for tiny boot helpers (paths, instance). Route and View classes stay lazy.
