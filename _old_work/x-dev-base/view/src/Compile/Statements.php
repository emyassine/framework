<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * `@directive` compile table. Unknown tags are left as-is (`@media` in CSS).
 */
final class Statements
{
    /**
     * @param $html string
     * @param $state State
     *
     * @return string
     */
    public static function compile(string $html, State $state): string
    {
        $replaced = preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            static fn (array $m): string => self::one($m, $state),
            $html,
        );

        return is_string($replaced) ? $replaced : $html;
    }

    /**
     * @param $match array<int, string>
     * @param $state State
     *
     * @return string
     */
    private static function one(array $match, State $state): string
    {
        $name = $match[1];
        if (str_contains($name, '@')) {
            return $name.($match[3] ?? '');
        }
        $expression = $match[3] ?? '';
        $custom = $state->directives->get($name);
        if ($custom !== null) {
            return $custom($expression);
        }
        $builtin = self::builtins()[$name] ?? null;
        if ($builtin !== null) {
            return $builtin($expression, $state);
        }

        return $match[0];
    }

    /**
     * @return array<string, callable(string, State): string>
     */
    private static function builtins(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $map = [
            'if' => static fn (string $e): string => Php::OPEN.'if'.$e.': ?>',
            'elseif' => static fn (string $e): string => Php::OPEN.'elseif'.$e.': ?>',
            'else' => static fn (string $_): string => Php::OPEN.'else: ?>',
            'endif' => static fn (string $_): string => Php::OPEN.'endif; ?>',
            'unless' => static fn (string $e): string => Php::OPEN.'if ( ! '.$e.'): ?>',
            'endunless' => static fn (string $_): string => Php::OPEN.'endif; ?>',
            'isset' => static fn (string $e): string => Php::OPEN.'if(isset'.$e.'): ?>',
            'endisset' => static fn (string $_): string => Php::OPEN.'endif; ?>',
            'foreach' => static fn (string $e): string => Php::OPEN.'foreach'.$e.': ?>',
            'endforeach' => static fn (string $_): string => Php::OPEN.'endforeach; ?>',
            'for' => static fn (string $e): string => Php::OPEN.'for'.$e.': ?>',
            'endfor' => static fn (string $_): string => Php::OPEN.'endfor; ?>',
            'while' => static fn (string $e): string => Php::OPEN.'while'.$e.': ?>',
            'endwhile' => static fn (string $_): string => Php::OPEN.'endwhile; ?>',
            'break' => static fn (string $e): string => $e !== '' ? Php::OPEN.'if'.$e.' break; ?>' : Php::OPEN.'break; ?>',
            'continue' => static fn (string $e): string => $e !== '' ? Php::OPEN.'if'.$e.' continue; ?>' : Php::OPEN.'continue; ?>',
            'php' => static fn (string $e): string => $e !== '' ? Php::OPEN.Php::strip_parentheses($e).'; ?>' : Php::OPEN,
            'endphp' => static fn (string $_): string => ' ?>',
            'section' => static fn (string $e): string => Php::OPEN.'$this->start_section'.$e.'; ?>',
            'endsection' => static fn (string $_): string => Php::OPEN.'$this->stop_section(); ?>',
            'stop' => static fn (string $_): string => Php::OPEN.'$this->stop_section(); ?>',
            'overwrite' => static fn (string $_): string => Php::OPEN.'$this->stop_section(true); ?>',
            'parent' => static fn (string $_): string => '@parentXYZABC',
            'yield' => static fn (string $e): string => Php::echo('$this->yield_content'.$e),
            'include' => static fn (string $e): string => Php::echo('$this->run_child('.Php::strip_parentheses($e).')'),
            'push' => static fn (string $e): string => Php::OPEN.'$this->start_push'.$e.'; ?>',
            'endpush' => static fn (string $_): string => Php::OPEN.'$this->stop_push(); ?>',
            'stack' => static fn (string $e): string => Php::echo(' $this->stack'.$e),
            'once' => static function (string $e): string {
                $key = Php::strip_parentheses($e);
                if ($key === '') {
                    $key = var_export('once_'.bin2hex(random_bytes(8)), true);
                }

                return Php::OPEN.'if ($this->once('.$key.')): ?>';
            },
            'endonce' => static fn (string $_): string => Php::OPEN.'endif; ?>',
            'csrf' => static fn (string $_): string => Php::echo('\\Webkernel\\Csrf::field()'),
            'auth' => static fn (string $_): string => Php::OPEN.'if (\\function_exists(\'auth\') && auth()->check()): ?>',
            'endauth' => static fn (string $_): string => Php::OPEN.'endif; ?>',
            'guest' => static fn (string $_): string => Php::OPEN.'if (! \\function_exists(\'auth\') || ! auth()->check()): ?>',
            'endguest' => static fn (string $_): string => Php::OPEN.'endif; ?>',
            'slot' => static fn (string $e): string => Php::OPEN.' $this->slot'.$e.'; ?>',
            'endslot' => static fn (string $_): string => Php::OPEN.' $this->end_slot(); ?>',
            'props' => static function (string $e): string {
                $list = Php::strip_parentheses($e);

                return Php::OPEN
                    .' $__props = '.$list.';'
                    .' foreach ($__props as $__n => $__d) { if (!isset($$__n)) { $$__n = $__d; } }'
                    .' $attributes = (isset($attributes) && $attributes instanceof \\Webkernel\\View\\AttributeBag'
                    .' ? $attributes : new \\Webkernel\\View\\AttributeBag([]))->except(\\array_keys($__props));'
                    .' unset($__props, $__n, $__d); ?>';
            },
            'component' => static fn (string $e): string => Php::OPEN.' $this->start_component'.$e.'; ?>',
            'endcomponent' => static fn (string $_): string => Php::echo('$this->render_component()'),
        ];

        $map['forelse'] = static function (string $e, State $state): string {
            $empty = '$__empty_'.++$state->forelse;

            return Php::OPEN.$empty.' = true; foreach'.$e.': '.$empty.' = false; ?>';
        };
        $map['empty'] = static function (string $e, State $state): string {
            if ($e === '') {
                $empty = '$__empty_'.$state->forelse--;

                return Php::OPEN.'endforeach; if ('.$empty.'): ?>';
            }

            return Php::OPEN.'if (empty'.$e.'): ?>';
        };
        $map['endforelse'] = static fn (string $_): string => Php::OPEN.'endif; ?>';
        $map['endempty'] = static fn (string $_): string => Php::OPEN.'endif; ?>';

        $map['extends'] = static function (string $e, State $state): string {
            $id = ++$state->extend_id;
            $view = Php::strip_parentheses($e);
            $state->footer[] = Php::OPEN.'if (isset($_shouldextend['.$id.'])) { echo $this->run_child('.$view.'); } ?>';

            return Php::OPEN.'$_shouldextend['.$id.']=1; ?>';
        };

        $map['can'] = static fn (string $e, State $state): string => Php::OPEN.'if ('.self::acl($e, $state, 'can').'): ?>';
        $map['cannot'] = static fn (string $e, State $state): string => Php::OPEN.'if (! '.self::acl($e, $state, 'can').'): ?>';
        $map['canany'] = static fn (string $e, State $state): string => Php::OPEN.'if ('.self::acl($e, $state, 'can_any').'): ?>';
        $map['elsecan'] = static fn (string $e, State $state): string => Php::OPEN.'elseif ('.self::acl($e, $state, 'can').'): ?>';
        $map['elsecannot'] = static fn (string $e, State $state): string => Php::OPEN.'elseif (! '.self::acl($e, $state, 'can').'): ?>';
        $map['elsecanany'] = static fn (string $e, State $state): string => Php::OPEN.'elseif ('.self::acl($e, $state, 'can_any').'): ?>';
        $map['endcan'] = static fn (string $_): string => Php::OPEN.'endif; ?>';
        $map['endcannot'] = static fn (string $_): string => Php::OPEN.'endif; ?>';
        $map['endcanany'] = static fn (string $_): string => Php::OPEN.'endif; ?>';

        return $map;
    }

    /**
     * @param $expression string
     * @param $state State
     * @param $method string
     *
     * @return string
     */
    private static function acl(string $expression, State $state, string $method): string
    {
        $v = Php::strip_parentheses($expression);
        $module = $state->acl_module;
        if (is_string($module) && $module !== '') {
            return 'webapp()->acl('.var_export($module, true).')->'.$method.'('.$v.')';
        }

        return 'webapp()->acl()->'.$method.'('.$v.')';
    }
}
