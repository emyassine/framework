<?php declare(strict_types=1);

if (! function_exists('webterminal')) {
    function webterminal(): \Webkernel\Console\Terminal
    {
        $container = webapp()->container();
        if (! $container->has(\Webkernel\Console\Terminal::class)) {
            $container->singleton(\Webkernel\Console\Terminal::class);
        }

        /** @var \Webkernel\Console\Terminal $terminal */
        $terminal = $container->make(\Webkernel\Console\Terminal::class);

        return $terminal;
    }
}
