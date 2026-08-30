{{--
  webkernel::panels.layout.utility-classes.index
  ──────────────────────────────────────────────
  Opt-in interaction utilities (Tailwind-like names).
  Each partial is self-contained (<style> + docs). Order matters for tokens.

    utility-classes/
      _tokens.blade.php         motion + shadow CSS vars
      _elevate.blade.php        elevate + hover:elevate
      _fi-stretch.blade.php     fi-stretch / fi-grid-equal-rows (equal card heights)
      _shadow.blade.php         shadow + hover:shadow-*
      _hover-border.blade.php   hover:primary-border, …
      _hover-bg.blade.php       hover:bg-gray / hover:background-primary-600, …
      _hover-motion.blade.php   scale / lift / opacity
      _focus.blade.php          focus:ring
      _group-hover.blade.php    group-hover:*
      _appear.blade.php         hover:appear / mobile:appear (child label on parent hover)
      _reduced-motion.blade.php prefers-reduced-motion
      _grid.blade.php           grid-container / grid-item
--}}
@php
echo (new class {
    public static function run(): string {
        $css = '';
        for ($i = 0; $i <= 200; $i++) {
            $css .= ".margin-inline-end-{$i}px{margin-inline-end:{$i}px}\n";
            $css .= ".margin-inline-end-{$i}rem{margin-inline-end:{$i}rem}\n";
        }
        return "<style>\n{$css}</style>";
    }
})->run();
@endphp

@include('webkernel::panels.layout.utility-classes._tokens')
@include('webkernel::panels.layout.utility-classes._elevate')
@include('webkernel::panels.layout.utility-classes._fi-stretch')
@include('webkernel::panels.layout.utility-classes._shadow')
@include('webkernel::panels.layout.utility-classes._hover-border')
@include('webkernel::panels.layout.utility-classes._hover-bg')
@include('webkernel::panels.layout.utility-classes._hover-motion')
@include('webkernel::panels.layout.utility-classes._focus')
@include('webkernel::panels.layout.utility-classes._group-hover')
@include('webkernel::panels.layout.utility-classes._appear')
@include('webkernel::panels.layout.utility-classes._reduced-motion')
@include('webkernel::panels.layout.utility-classes._grid')
