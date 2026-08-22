# webkernel/performance

OPcache and JIT status for the current engine. JIT is a process-start flag (`php.ini` or `php -d`). It cannot be switched on with `ini_set()`.

```php
if (! webapp()->performance()->is_jit()) {
    webapp()->performance()->enable_jit(); // persist; restart the process
}
```

Compiled views are PHP. With JIT on, the Zend VM compiles hot compiled-view files to machine code. There is no Composer package faster than this inside PHP.

Dev server:

```
php webkernel server --with-jit
```
