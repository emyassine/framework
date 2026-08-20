# webkernel/views

Compile-once templates. Cached under `bootstrap/cache/webkernel/views/`.

```php
echo view('home', ['title' => 'Hi']);
view_path('/path/to/more/views');
```

Looks in `webapp_path('views')` then this package `views/`.

```
{{ $title }}           escaped
{!! $html !!}          raw
@if ($ok) … @endif
@foreach ($rows as $row) … @endforeach
@include('components/alert')
```

Do not put `@if` inside an HTML tag. Compute locals, then always emit the attribute.

License: EPL-2.0.
