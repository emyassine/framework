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
    "prefix": "views",
    "provider": "Webkernel\\View\\ViewProvider",
    "views": "views"
  }
}
```

Providers declare view/component dirs at boot (`declare_view('webkernel', $dir)`). Dump `webkernel_views.php` is fallback. Namespaced: `@include('webkernel::layouts.page')`, `<webkernel::page />`. Un-namespaced `@extends('layouts.page')` stays. Route and View classes stay lazy until `webapp()->view()` / `webapp()->route()`.
