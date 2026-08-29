<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

if (! function_exists('icon')) {
    /**
     * SVG markup for an icon basename (without .svg).
     */
    function icon(string $name, string $class = '', string $style = '', string $set = 'lucide'): string
    {
        $svg = \Webkernel\Imagery\Icon::svg($name, $set);
        if ($svg === '') {
            return '';
        }
        $inject = '';
        if ($class !== '') {
            $inject .= ' class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'"';
        }
        if ($style !== '') {
            $inject .= ' style="'.htmlspecialchars($style, ENT_QUOTES, 'UTF-8').'"';
        }
        if ($inject !== '' && str_starts_with($svg, '<svg')) {
            $svg = substr_replace($svg, $inject, 4, 0);
        }

        return $svg;
    }
}
