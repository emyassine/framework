<?php
// ponytail: BladeOne engine is not strict_types — add it when the compiler is specialized.
/**
 * @noinspection PhpUnusedParameterInspection
 * @noinspection SyntaxError
 * @noinspection ForgottenDebugOutputInspection
 * @noinspection UnknownInspectionInspection
 * @noinspection TypeUnsafeComparisonInspection
 * @noinspection NonSecureExtractUsageInspection
 * @noinspection PregQuoteUsageInspection
 * @noinspection NotOptimalRegularExpressionsInspection
 * @noinspection SubStrUsedAsStrPosInspection
 * @noinspection ThrowRawExceptionInspection
 * @noinspection Annotator
 * @noinspection IsNullFunctionUsageInspection
 * @noinspection CallableParameterUseCaseInTypeContextInspection
 * @noinspection PhpUnused
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Webkernel\View;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
use Exception;
use InvalidArgumentException;

/**
 * Compiler — BladeOne (EFTEC) owned by Webkernel. Do not require eftec/bladeone.
 * Templates: .view.php. Compiled: .view.php.compiled.
 *
 * @package   Compiler
 * @author    Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 * @copyright Copyright (c) 2016-2025 Jorge Patricio Castro Castillo MIT License.
 *            Don't delete this comment, its part of the license.
 *            Part of this code is based on the work of Laravel PHP Components.
 * @version   4.19.1
 * @link      https://github.com/EFTEC/BladeOne
 */
class Compiler
{
    //<editor-fold desc="fields">
    public const VERSION = '4.19.1';
    /** @var int Compiler reads if the compiled file has changed. If it has changed, then the file is replaced. */
    public const MODE_AUTO = 0;
    /** @var int The compiled file is always replaced. It's slow and it's useful for development. */
    public const MODE_SLOW = 1;
    /** @var int The compiled file is never replaced. It's fast and it's useful for production. */
    public const MODE_FAST = 2;
    /** @var int DEBUG MODE, the file is always compiled and the filename is identifiable. */
    public const MODE_DEBUG = 5;
    /** @var array Hold dictionary of translations */
    public static array $dictionary = [];
    /** @var string It is used to mark the start of the stack (regexp). This value must not be used for other purposes */
    public string $escape_stack0 = '-#1Z#-#2B#';
    /** @var string It is used to mark the end of the stack (regexp). This value must not be used for other purposes */
    public string $escape_stack1 = '#3R#-#4X#-';
    /** @var string PHP tag. You could use < ?php or < ? (if shorttag is active in php.ini) */
    public string $php_tag = '<?php ';
    /** @var string this line is used to easily echo a value */
    protected string $php_tag_echo = '<?php' . ' echo ';
    /** @var string|null $current_user Current user. Example: john */
    public ?string $current_user;
    /** @var string|null $current_role Current role. Example: admin */
    public ?string $current_role;
    /** @var string[]|null $current_permission Current permission. Example ['edit','add'] */
    public ?array $current_permission = [];
    /** @var callable|null callback of validation. It is used for "@can,@cannot" */
    public $auth_call_back;
    /** @var callable|null callback of validation. It is used for @canany */
    public $auth_any_call_back;
    /** @var callable|null callback of errors. It is used for @error */
    public $error_call_back;
    /** @var bool if true then, if the operation fails, and it is critic, then it throws an error */
    public bool $throw_on_error = false;
    /** @var string security token */
    public string $csrf_token = '';
    /** @var string The path to the missing translations log file. If empty, then every missing key is not saved. */
    public string $missing_log = '';
    /** @var bool if true then pipes commands are available, example {{$a1|strtolower}} */
    public bool $pipe_enable = false;
    /** @var array Alias (with or without namespace) of the classes */
    public array $alias_classes = [];
    protected array $hierarcy = [];
    /**
     * @var callable[] associative array with the callable methods. The key must be the name of the method<br>
     *                 **example:**<br>
     *                 ```
     *                 $this->methods['compileAlert']=static function(?string $expression=null) { return };
     *                 $this->methods['runtimeAlert']=function(?array $arguments=null) { return };
     *                 ```
     */
    protected array $methods = [];
    protected array $control_stack = [['name' => '', 'args' => [], 'parent' => 0]];
    protected int $control_stack_parent = 0;
    /** @var Compiler it is used to get the last instance */
    public static Compiler $instance;
    /**
     * @var bool if it is true, then the variables defined in the "include" as arguments are scoped to work only
     * inside the "include" statement.<br>
     * If false (default value), then the variables defined in the "include" as arguments are defined globally.<br>
     * <b>Example: (include_scope=false)</b><br>
     * include("template",['a1'=>'abc']) // a1 is equals to abc<br>
     * include("template",[]) // a1 is equals to abc<br>
     * <br><b>Example: (include_scope=true)</b><br>
     * include("template",['a1'=>'abc']) // a1 is equals to abc<br>
     * include("template",[]) // a1 is not defined<br>
     */
    public bool $include_scope = false;
    /**
     * @var callable[] It allows to parse the compiled output using a function.
     *      This function doesn't require to return a value<br>
     *      **Example:** this converts all compiled result in uppercase (note, content is a ref)
     *      ```
     *      $this->compile_callbacks[]= static function (&$content, $templatename=null) {
     *      $content=strtoupper($content);
     *      };
     *      ```
     */
    public array $compile_callbacks = [];
    /** @var array All the registered extensions. */
    protected array $extensions = [];
    /** @var array All the finished, captured sections. */
    protected array $sections = [];
    /** @var string The template currently being compiled. For example "folder.template" */
    protected string $file_name = "";
    protected string $current_view = "";
    protected string $not_found_path = "";
    /** @var string File extension for the template files. */
    protected string $file_extension = '.view.php';
    /** @var array The stack of in-progress sections. */
    protected array $section_stack = [];
    /** @var array The stack of in-progress loops. */
    protected array $loops_stack = [];
    /** @var array Dictionary of variables */
    protected array $variables = [];
    /** @var array Dictionary of global variables */
    protected array $variables_global = [];
    /** @var array All the available compiler functions. */
    protected array $compilers = [
        'extensions',
        'components',
        'statements',
        'comments',
        'echos',
    ];
    /** @var string|null it allows to set the stack */
    protected ?string $view_stack = null;
    /** @var array used by $this->composer() */
    protected array $composer_stack = [];
    /** @var array The stack of in-progress push sections. */
    protected array $push_stack = [];
    /** @var array All the finished, captured push sections. */
    protected array $pushes = [];
    /** @var int The number of active rendering operations. */
    protected int $render_count = 0;
    /** @var string[] Get the template path for the compiled views. */
    protected array $template_path = [];
    /** @var array<string, list<string>> namespaced view dirs */
    protected array $view_namespaces = [];
    /** @var array<string, list<string>> namespaced component dirs */
    protected array $component_namespaces = [];
    /** @var string|null Get the compiled path for the compiled views. If null then it uses the default path */
    protected ?string $compile_dpath = null;
    /** @var string the extension of the compiled file. */
    protected string $compile_extension = '.view.php.compiled';
    /**
     * @var string=['auto','sha1','md5'][$i] It determines how the compiled filename will be called.<br>
     *            **auto** (default mode) the mode is "sha1"<br>
     *            **sha1** the filename is converted into a sha1 hash<br>
     *            **md5** the filename is converted into a md5 hash<br>
     */
    protected string $compile_typefilename = 'auto';
    /** @var array Custom "directive" dictionary. Those directives run at compile time. */
    protected array $custom_directives = [];
    /** @var bool[] Custom directive dictionary. Those directives run at runtime. */
    protected array $custom_directives_rt = [];
    /** @var callable Function used for resolving injected classes. */
    protected $inject_resolver;
    /** @var array Used for conditional if. */
    protected array $conditions = [];
    /** @var int Unique counter. It's used for extends */
    protected int $uid_counter = 0;
    /** @var string The main url of the system. Don't use raw $_SERVER values unless the value is sanitized */
    protected string $base_url = '.';
    protected string $cdn_url = '.';
    /** @var string|null The base domain of the system */
    protected ?string $base_domain;
    /** @var string|null It stores the current canonical url. */
    protected ?string $canonical_url;
    /** @var string|null It stores the current url including arguments */
    protected ?string $current_url;
    /** @var string it is a relative path calculated between base_url and the current url. Example ../../ */
    protected string $relative_path = '';
    /** @var string[] Dictionary of assets */
    protected array $asset_dict = [];
    protected array $asset_dict_cdn = [];
    /** @var bool if true then it removes tabs and unneeded spaces */
    protected bool $optimize = true;
    /** @var bool if false, then the template is not compiled (but executed on memory). */
    protected bool $is_compiled = true;
    /** @var bool */
    protected bool $is_run_fast = false; // stored for historical purpose.
    /** @var array Array of opening and closing tags for raw echos. */
    protected array $raw_tags = ['{!!', '!!}'];
    /** @var array Array of opening and closing tags for regular echos. */
    protected array $content_tags = ['{{', '}}'];
    protected int $comment_mode = 0;
    /** @var array Array of opening and closing tags for escaped echos. */
    protected array $escaped_tags = ['{{{', '}}}'];
    /** @var string The "regular" / legacy echo string format. */
    protected string $echo_format = '\htmlentities(%s??\'\', ENT_QUOTES, \'UTF-8\', false)';
    /** @var string */
    protected string $echo_format_old = 'static::e(%s)';
    /** @var array Lines that will be added at the footer of the template */
    protected array $footer = [];
    /** @var string Placeholder to temporary mark the position of verbatim blocks. */
    protected string $verbatim_placeholder = '$__verbatim__$';
    /** @var array Array to temporary store the verbatim blocks found in the template. */
    protected array $verbatim_blocks = [];
    /** @var int Counter to keep track of nested forelse statements. */
    protected int $forelse_counter = 0;
    /** @var array The components being rendered. */
    protected array $component_stack = [];
    /** @var array The original data passed to the component. */
    protected array $component_data = [];
    /** @var array The slot contents for the component. */
    protected array $slots = [];
    /** @var array The names of the slots being rendered. */
    protected array $slot_stack = [];
    /** @var string tag unique */
    protected string $PARENTKEY = '@parentXYZABC';
    /**
     * Indicates the compile mode.
     * if the constant BLADEONE_MODE is defined, then it is used instead of this field.
     *
     * @var int=[Compiler::MODE_AUTO,Compiler::MODE_DEBUG,Compiler::MODE_SLOW,Compiler::MODE_FAST][$i]
     */
    protected int $mode;
    /** @var int Indicates the number of open switches */
    protected int $switch_count = 0;
    /** @var bool Indicates if the switch is recently open */
    protected bool $first_case_in_switch = true;
    //</editor-fold>
    //<editor-fold desc="constructor">
    /**
     * It creates an instance of Compiler. The folder at $compile_dpath is created in case it doesn't exist.<br>
     * **Example**
     * ```
     * $blade=new Compiler("pathtemplate","pathcompile",Compiler::MODE_AUTO,2);
     * ```
     *
     * @param string|null $template_path If null then it uses (caller_folder)/views
     * @param string|null $compile_dpath If null then it uses (caller_folder)/compiles
     * @param int         $mode         =[Compiler::MODE_AUTO,Compiler::MODE_DEBUG,Compiler::MODE_FAST,Compiler::MODE_SLOW][$i]<br>
     *                                  **Compiler::MODE_AUTO** (default mode)<br>
     *                                  **Compiler::MODE_DEBUG** errors will be more verbose, and it will compile code
     *                                  every time<br>
     *                                  **Compiler::MODE_FAST** it will not check if the compiled file exists<br>
     *                                  **Compiler::MODE_SLOW** it will compile the code everytime<br>
     * @param int         $comment_mode  =[0,1,2][$i] <br>
     *                                  **0** comments are generated as php code.<br>
     *                                  **1** comments are generated as html code<br>
     *                                  **2** comments are ignored (no code is generated)<br>
     */
    public function __construct($template_path = null, $compile_dpath = null, $mode = 0, $comment_mode = 0)
    {
        if ($template_path === null) {
            $template_path = \getcwd() . '/views';
        }
        if ($compile_dpath === null) {
            $compile_dpath = \getcwd() . '/compiles';
        }
        $this->template_path = (is_array($template_path)) ? $template_path : [$template_path];
        $this->compile_dpath = $compile_dpath;
        $this->set_mode($mode);
        $this->set_comment_mode($comment_mode);
        self::$instance = $this;
        $this->auth_call_back = function(
            $action = null,
            /** @noinspection PhpUnusedParameterInspection */
            $subject = null
        ) {
            return \in_array($action, $this->current_permission, true);
        };
        $this->auth_any_call_back = function($array = []) {
            foreach ($array as $permission) {
                if (\in_array($permission, $this->current_permission ?? [], true)) {
                    return true;
                }
            }
            return false;
        };
        $this->error_call_back = static function(
            /** @noinspection PhpUnusedParameterInspection */
            $key = null
        ) {
            return false;
        };
        // If the "traits" has "Constructors", then we call them.
        // Requisites.
        // 1- the method must be public or protected
        // 2- it must don't have arguments
        // 3- It must have the name of the trait. i.e. trait=MyTrait, method=MyTrait()
        $traits = get_declared_traits();
        $currentTraits = (array)class_uses($this);
        foreach ($traits as $trait) {
            $r = explode('\\', $trait);
            $name = end($r);
            if (!in_array($trait, $currentTraits, true)) {
                continue;
            }
            if (is_callable([$this, $name]) && method_exists($this, $name)) {
                $this->{$name}();
            }
        }
    }

    /**
     * It gets an instance of Bladeone or will create a new one. This function is useful if you want a singleton<br>
     * **Example**
     * ```
     * $blade=Compiler::get_instance();
     * $blade=Compiler::get_instance("templatepath","compilepath",Compiler::MODE_AUTO,0);
     * ```
     * @param string|array $template_path If null then it uses (caller_folder)/views
     * @param string       $compile_dpath If null then it uses (caller_folder)/compiles
     * @param int          $mode         =[Compiler::MODE_AUTO,Compiler::MODE_DEBUG,Compiler::MODE_FAST,Compiler::MODE_SLOW][$i]<br>
     *                                   **Compiler::MODE_AUTO** (default mode)<br>
     *                                   **Compiler::MODE_DEBUG** errors will be more
     *                                   verbose, and it will compile code every time<br>
     *                                   **Compiler::MODE_FAST** it will not check if the
     *                                   compiled file exists<br>
     *                                   **Compiler::MODE_SLOW** it will compile the code
     *                                   everytime<br>
     * @param int          $comment_mode  =[0,1,2][$i] <br>
     *                                   **0** comments are generated as php code.<br>
     *                                   **1** comments are generated as html code<br>
     *                                   **2** comments are ignored (no code is
     *                                   generated)<br>
     * @return Compiler
     */
    public static function get_instance($template_path = null, $compile_dpath = null, $mode = 0, $comment_mode = 0): Compiler
    {
        if (self::$instance === null) {
            new self($template_path, $compile_dpath, $mode, $comment_mode);
        }
        return self::$instance;
    }

    /**
     * It adds a control to the stack<br>
     * **Example:**<br>
     * ```
     * $this->add_control_stack_child('alert',['message'=>'hello']);
     * ```
     * @param string $name the nametag of the stack
     * @param array  $args
     * @return void
     */
    public function add_control_stack_child(string $name, array $args): void
    {
        $this->control_stack[] = ['name' => $name, 'args' => $args, 'parent' => $this->control_stack_parent];
        $this->control_stack_parent = array_key_last($this->control_stack);
    }

    public function add_control_stack_sibling(string $name, array $args): void
    {
        $grandparent = $this->control_stack[$this->control_stack_parent]['parent'];
        $this->control_stack[] = ['name' => $name, 'args' => $args, 'parent' => $grandparent];
    }

    /**
     * It returns the lastest control from the stack and removes it.
     * @return mixed|null
     */
    public function close_control_stack()
    {
        $this->control_stack_parent = $this->control_stack[$this->control_stack_parent]['parent'];
        return array_pop($this->control_stack);
    }

    /**
     * It removes the last parent and returns the new parent (the previous grandparent)<br>
     * Usually this method and close_control_stack must return the same if every child was closed correctly.
     * @return mixed|null
     */
    public function close_control_stack_parent()
    {
        $grandparent = $this->control_stack[$this->control_stack_parent]['parent'];
        unset($this->control_stack[$this->control_stack_parent]);
        $this->control_stack_parent = $grandparent;
        return $this->control_stack[$this->control_stack_parent];
    }

    /**
     * It returns the last control from the stack without removing it.<br>
     * It is useful to get the previous control, it could be a parent or a sibling.
     * @return array
     */
    public function last_control_stack(): array
    {
        return @end($this->control_stack);
    }

    /**
     * It gets the parent control stack
     * @return array
     */
    public function parent_control_stack(): array
    {
        return $this->control_stack[$this->control_stack_parent];
    }

    /**
     * It clears the whole control stack
     * @return void
     */
    public function clear_control_stack(): void
    {
        $this->control_stack = [['name' => '', 'args' => [], 'parent' => 0]];
    }

    /**
     * It adds a new method<br>
     * **Example:**<br>
     * ```
     * $this->add_method('compile','alert',static function(?string $expression=null) { return });
     * $this->add_method('runtime','alert',function(?array $arguments=null) { return });
     * ```
     * @param string   $type     =['compile','runtime'][$i] if you want to add a compile method or a runtime method
     * @param string   $name     the name of the method. Commonly it is in lowercase.
     * @param callable $callable the callable method
     * @return Compiler
     */
    public function add_method(string $type, string $name, callable $callable): Compiler
    {
        $fullName = $type . ucfirst($name);
        $this->methods[$fullName] = $callable;
        return $this;
    }

    /**
     * It clears all the methods defined.
     * @return $this
     */
    public function clear_methods(): self
    {
        $this->methods = [];
        return $this;
    }
    //</editor-fold>
    //<editor-fold desc="common">
    /**
     * Show an error in the web.
     *
     * @param string $id          Title of the error
     * @param string $text        Message of the error
     * @param bool   $critic      if true then the compilation is ended, otherwise it continues
     * @param bool   $alwaysThrow if true then it always throws a runtime exception.
     * @return string
     * @throws \RuntimeException
     */
    public function show_error($id, $text, $critic = false, $alwaysThrow = false): string
    {
        \ob_get_clean();
        if ($this->throw_on_error || $alwaysThrow || $critic === true) {
            throw new \RuntimeException("Compiler Error [$id] $text");
        }
        $msg = "<div style='background-color: red; color: black; padding: 3px; border: solid 1px black;'>";
        $msg .= "Compiler Error [$id]:<br>";
        $msg .= "<span style='color:white'>$text</span><br></div>\n";
        echo $msg;
        if ($critic) {
            die(1);
        }
        return $msg;
    }

    /**
     * Escape HTML entities in a string.
     *
     * @param int|string|null $value
     * @return string
     */
    public static function e($value): string
    {
        // Prevent "Deprecated: htmlentities(): Passing null to parameter #1 ($string) of type string is deprecated" message
        if (\is_null($value)) {
            return '';
        }
        if (\is_array($value) || \is_object($value)) {
            return \htmlentities(\print_r($value, true), ENT_QUOTES, 'UTF-8', false);
        }
        if (\is_numeric($value)) {
            $value = (string)$value;
        }
        return \htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    protected static function convert_arg_call_back($k, $v): string
    {
        return $k . "='$v' ";
    }

    /**
     * @param mixed|\DateTime $variable
     * @param string|null     $format
     * @return string
     */
    public function format($variable, $format = null): string
    {
        if ($variable instanceof \DateTime) {
            $format = $format ?? 'Y/m/d';
            return $variable->format($format);
        }
        $format = $format ?? '%s';
        return sprintf($format, $variable);
    }

    /**
     * It converts a text into a php code with echo<br>
     * **Example:**<br>
     * ```
     * $this->wrap_php('$hello'); // "< ?php echo $this->e($hello); ? >"
     * $this->wrap_php('$hello',''); // < ?php echo $this->e($hello); ? >
     * $this->wrap_php('$hello','',false); // < ?php echo $hello; ? >
     * $this->wrap_php('"hello"'); // "< ?php echo $this->e("hello"); ? >"
     * $this->wrap_php('hello()'); // "< ?php echo $this->e(hello()); ? >"
     * ```
     *
     * @param ?string $input The input value
     * @param string  $quote The quote used (to quote the result)
     * @param bool    $parse If the result will be parsed or not. If false then it's returned without $this->e
     * @return string
     */
    public function wrap_php($input, $quote = '"', $parse = true): string
    {
        if ($input === null) {
            return 'null';
        }
        if (strpos($input, '(') !== false && !$this->is_quoted($input)) {
            if ($parse) {
                return $quote . $this->php_tag_echo . '$this->e(' . $input . ');?>' . $quote;
            }
            return $quote . $this->php_tag_echo . $input . ';?>' . $quote;
        }
        if (strpos($input, '$') === false) {
            if ($parse) {
                return self::enq($input);
            }
            return $input;
        }
        if ($parse) {
            return $quote . $this->php_tag_echo . '$this->e(' . $input . ');?>' . $quote;
        }
        return $quote . $this->php_tag_echo . $input . ';?>' . $quote;
    }

    /**
     * Returns true if the text is surrounded by quotes (double or single quote)
     *
     * @param string|null $text
     * @return bool
     */
    public function is_quoted($text): bool
    {
        if (!$text || strlen($text) < 2) {
            return false;
        }
        if ($text[0] === '"' && substr($text, -1) === '"') {
            return true;
        }
        return ($text[0] === "'" && substr($text, -1) === "'");
    }

    /**
     * Escape HTML entities in a string.
     *
     * @param string $value
     * @return string
     */
    public static function enq($value): string
    {
        if (\is_array($value) || \is_object($value)) {
            return \htmlentities(\print_r($value, true), ENT_NOQUOTES, 'UTF-8', false);
        }
        return \htmlentities($value ?? '', ENT_NOQUOTES, 'UTF-8', false);
    }

    /**
     * @param string      $view  example "folder.template"
     * @param string|null $alias example "mynewop". If null then it uses the name of the template.
     */
    public function add_include($view, $alias = null): void
    {
        if (!isset($alias)) {
            $alias = \explode('.', $view);
            $alias = \end($alias);
        }
        $this->directive($alias, function($expression) use ($view) {
            $expression = $this->strip_parentheses($expression) ?: '[]';
            return "$this->php_tag echo \$this->run_child('$view', $expression); ?>";
        });
    }

    /**
     * Register a handler for custom directives.
     *
     * @param string   $name
     * @param callable $handler
     * @return void
     */
    public function directive($name, callable $handler): void
    {
        $this->custom_directives[$name] = $handler;
        $this->custom_directives_rt[$name] = false;
    }

    /**
     * Strip the parentheses from the given expression.
     *
     * @param string|null $expression
     * @return string
     */
    public function strip_parentheses($expression): string
    {
        if (\is_null($expression)) {
            return '';
        }
        if (static::starts_with($expression, '(')) {
            $expression = \substr($expression, 1, -1);
        }
        return $expression;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function starts_with($haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '') {
                if (\function_exists('mb_strpos')) {
                    if ($haystack !== null && \mb_strpos($haystack, $needle) === 0) {
                        return true;
                    }
                } elseif ($haystack !== null && \strpos($haystack, $needle) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * If false then the file is not compiled, and it is executed directly from the memory.<br>
     * By default the value is true<br>
     * It also sets the mode to MODE_SLOW
     *
     * @param bool $bool
     * @return Compiler
     * @see Compiler::set_mode
     */
    public function set_is_compiled($bool = false): Compiler
    {
        $this->is_compiled = $bool;
        if (!$bool) {
            $this->set_mode(self::MODE_SLOW);
        }
        return $this;
    }

    /**
     * It sets the template and compile path (without trailing slash).
     * <p>Example:set_path("somefolder","otherfolder");
     *
     * @param null|string|string[] $template_path If null then it uses the current path /views folder
     * @param null|string          $compile_dpath If null then it uses the current path /views folder
     */
    public function set_path($template_path, $compile_dpath): void
    {
        if ($template_path === null) {
            $template_path = \getcwd() . '/views';
        }
        if ($compile_dpath === null) {
            $compile_dpath = \getcwd() . '/compiles';
        }
        $this->template_path = (is_array($template_path)) ? $template_path : [$template_path];
        $this->compile_dpath = $compile_dpath;
    }

    public function set_echo_format(string $format): void
    {
        $this->echo_format = $format;
    }

    /**
     * @return list<string>
     */
    public function template_paths(): array
    {
        return $this->template_path;
    }

    public function add_template_path(string $path): void
    {
        $this->template_path[] = rtrim($path, '/\\');
    }

    public function add_view_namespace(string $namespace, string $path): void
    {
        $this->view_namespaces[$namespace][] = rtrim($path, '/\\');
    }

    public function add_component_namespace(string $namespace, string $path): void
    {
        $this->component_namespaces[$namespace][] = rtrim($path, '/\\');
    }

    /**
     * @return array
     */
    public function get_alias_classes(): array
    {
        return $this->alias_classes;
    }

    /**
     * @param array $alias_classes
     */
    public function set_alias_classes($alias_classes): void
    {
        $this->alias_classes = $alias_classes;
    }

    /**
     * @param string $aliasName
     * @param string $classWithNS
     */
    public function add_alias_classes($aliasName, $classWithNS): void
    {
        $this->alias_classes[$aliasName] = $classWithNS;
    }
    //</editor-fold>
    //<editor-fold desc="compile">
    /**
     * Authentication. Sets with a user,role and permission
     *
     * @param string $user
     * @param null   $role
     * @param array  $permission
     */
    public function set_auth($user = '', $role = null, $permission = []): void
    {
        $this->current_user = $user;
        $this->current_role = $role;
        $this->current_permission = $permission;
    }

    /**
     * run the blade engine. It returns the result of the code.
     *
     * @param string $string HTML to parse
     * @param array  $data   It is an associative array with the datas to display.
     * @return string It returns a parsed string
     * @throws Exception
     */
    public function run_string($string, $data = []): string
    {
        $php = $this->compile_string($string);
        $obLevel = \ob_get_level();
        \ob_start();
        \extract($data, EXTR_SKIP);
        $previousError = \error_get_last();
        try {
            @eval('?' . '>' . $php);
        } catch (Exception $e) {
            while (\ob_get_level() > $obLevel) {
                \ob_end_clean();
            }
            throw $e;
        } catch (\Throwable $e) { // PHP >= 7
            while (\ob_get_level() > $obLevel) {
                \ob_end_clean();
            }
            $this->show_error('run_string', $e->getMessage() . ' ' . $e->getCode(), true);
            return '';
        }
        $lastError = \error_get_last(); // PHP 5.6
        if ($previousError != $lastError && $lastError['type'] == E_PARSE) {
            while (\ob_get_level() > $obLevel) {
                \ob_end_clean();
            }
            $this->show_error('run_string', $lastError['message'] . ' ' . $lastError['type'], true);
            return '';
        }
        return $this->post_run(\ob_get_clean());
    }

    /**
     * Compile the given Blade template contents.
     *
     * @param string $value
     * @return string
     */
    public function compile_string($value): string
    {
        $result = '';
        if (\strpos($value, '@verbatim') !== false) {
            $value = $this->store_verbatim_blocks($value);
        }
        $this->footer = [];
        // Here we will loop through all the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
        foreach (\token_get_all($value) as $token) {
            $result .= \is_array($token) ? $this->parse_token($token) : $token;
        }
        if (!empty($this->verbatim_blocks)) {
            $result = $this->restore_verbatim_blocks($result);
        }
        // If there are any footer lines that need to get added to a template we will
        // add them here at the end of the template. This gets used mainly for the
        // template inheritance via the extends keyword that should be appended.
        if (\count($this->footer) > 0) {
            $result = \ltrim($result, PHP_EOL)
                . PHP_EOL . \implode(PHP_EOL, \array_reverse($this->footer));
        }
        return $result;
    }

    /**
     * Store the verbatim blocks and replace them with a temporary placeholder.
     *
     * @param string $value
     * @return string
     */
    protected function store_verbatim_blocks($value): string
    {
        return \preg_replace_callback('/(?<!@)@verbatim(.*?)@endverbatim/s', function($matches) {
            $this->verbatim_blocks[] = $matches[1];
            return $this->verbatim_placeholder;
        }, $value);
    }

    /**
     * Parse the tokens from the template.
     *
     * @param array $token
     *
     * @return string
     *
     * @see Compiler::compile_statements
     * @see Compiler::compile_extends
     * @see Compiler::compile_comments
     * @see Compiler::compile_echos
     */
    protected function parse_token($token): string
    {
        [$id, $content] = $token;
        if ($id == T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile_$type"}($content);
            }
        }
        return $content;
    }

    /**
     * Replace the raw placeholders with the original code stored in the raw blocks.
     *
     * @param string $result
     * @return string
     */
    protected function restore_verbatim_blocks($result): string
    {
        $result = \preg_replace_callback('/' . \preg_quote($this->verbatim_placeholder) . '/', function() {
            return \array_shift($this->verbatim_blocks);
        }, $result);
        $this->verbatim_blocks = [];
        return $result;
    }

    /**
     * it calculates the relative path of a web.<br>
     * This function uses the current url and the baseurl
     *
     * @param string $relativeWeb . Example img/images.jpg
     * @return string  Example ../../img/images.jpg
     */
    public function relative($relativeWeb): string
    {
        return $this->asset_dict[$relativeWeb] ?? ($this->relative_path . $relativeWeb);
    }

    /**
     * It adds an alias to the link of the resources.<br>
     * add_asset_dict('name','url/res.jpg')<br>
     * add_asset_dict(['name'=>'url/res.jpg','name2'=>'url/res2.jpg']);
     *
     * @param string|array $name example 'css/style.css', you could also add an array
     * @param string       $url  example https://www.web.com/style.css'
     */
    public function add_asset_dict($name, $url = ''): void
    {
        if (\is_array($name)) {
            $this->asset_dict = \array_merge($this->asset_dict, $name);
        } else {
            $this->asset_dict[$name] = $url;
        }
    }

    public function add_asset_dict_cdn($name, $url = ''): void
    {
        if (\is_array($name)) {
            $this->asset_dict_cdn = \array_merge($this->asset_dict_cdn, $name);
        } else {
            $this->asset_dict_cdn[$name] = $url;
        }
    }

    /**
     * Compile the push statements into valid PHP.
     *
     * @param string $expression
     * @return string
     * @see Compiler::start_push
     */
    public function compile_push($expression): string
    {
        return $this->php_tag . "\$this->start_push$expression; ?>";
    }

    /**
     * Compile the push statements into valid PHP.
     *
     * @param string $expression
     * @return string
     * @see Compiler::start_push
     */
    public function compile_pushonce($expression): string
    {
        $key = '$__pushonce__' . \trim(\substr($expression, 2, -2));
        return $this->php_tag . "if(!isset($key)): $key=1;  \$this->start_push$expression; ?>";
    }

    /**
     * Compile the push statements into valid PHP.
     *
     * @param string $expression
     * @return string
     * @see Compiler::start_push
     */
    public function compile_prepend($expression): string
    {
        return $this->php_tag . "\$this->start_push$expression; ?>";
    }

    /**
     * Start injecting content into a push section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    public function start_push($section, $content = ''): void
    {
        if ($content === '') {
            if (\ob_start()) {
                $this->push_stack[] = $section;
            }
        } else {
            $this->extend_push($section, $content);
        }
    }

    /*
     * endswitch tag
     */
    /**
     * Append content to a given push section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    protected function extend_push($section, $content): void
    {
        if (!isset($this->pushes[$section])) {
            $this->pushes[$section] = []; // start an empty section
        }
        if (!isset($this->pushes[$section][$this->render_count])) {
            $this->pushes[$section][$this->render_count] = $content;
        } else {
            $this->pushes[$section][$this->render_count] .= $content;
        }
    }

    /**
     * Start injecting content into a push section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    public function start_prepend($section, $content = ''): void
    {
        if ($content === '') {
            if (\ob_start()) {
                \array_unshift($this->push_stack[], $section);
            }
        } else {
            $this->extend_push($section, $content);
        }
    }

    /**
     * Stop injecting content into a push section.
     *
     * @return string
     */
    public function stop_push(): string
    {
        if (empty($this->push_stack)) {
            $this->show_error('stop_push', 'Cannot end a section without first starting one', true);
        }
        $last = \array_pop($this->push_stack);
        $this->extend_push($last, \ob_get_clean());
        return $last;
    }

    /**
     * Stop injecting content into a push section.
     *
     * @return string
     */
    public function stop_prepend(): string
    {
        if (empty($this->push_stack)) {
            $this->show_error('stop_prepend', 'Cannot end a section without first starting one', true);
        }
        $last = \array_shift($this->push_stack);
        $this->extend_start_push($last, \ob_get_clean());
        return $last;
    }

    /**
     * Append content to a given push section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    protected function extend_start_push($section, $content): void
    {
        if (!isset($this->pushes[$section])) {
            $this->pushes[$section] = []; // start an empty section
        }
        if (!isset($this->pushes[$section][$this->render_count])) {
            $this->pushes[$section][$this->render_count] = $content;
        } else {
            $this->pushes[$section][$this->render_count] = $content . $this->pushes[$section][$this->render_count];
        }
    }

    /**
     * Get the string contents of a push section.
     *
     * @param string $section the name of the section
     * @param string $default the default name of the section is not found.
     * @return string
     */
    public function yield_push_content($section, $default = ''): string
    {
        if ($section === null || $section === '') {
            return $default;
        }
        if ($section[-1] === '*') {
            $keys = array_keys($this->pushes);
            $findme = rtrim($section, '*');
            $result = "";
            foreach ($keys as $key) {
                if (strpos($key, $findme) === 0) {
                    $result .= \implode(\array_reverse($this->pushes[$key]));
                }
            }
            return $result;
        }
        if (!isset($this->pushes[$section])) {
            return $default;
        }
        return \implode(\array_reverse($this->pushes[$section]));
    }

    /**
     * Get the string contents of a push section.
     *
     * @param int|string $each if "int", then it split the foreach every $each numbers.<br>
     *                         if "string" or "c3", then it means that it will split in 3 columns<br>
     * @param string     $splitText
     * @param string     $splitEnd
     * @return string
     */
    public function split_foreach($each = 1, $splitText = ',', $splitEnd = ''): string
    {
        $loopStack = static::last($this->loops_stack); // array(7) { ["index"]=> int(0) ["remaining"]=> int(6) ["count"]=> int(5) ["first"]=> bool(true) ["last"]=> bool(false) ["depth"]=> int(1) ["parent"]=> NULL }
        if (($loopStack['index']) == $loopStack['count'] - 1) {
            return $splitEnd;
        }
        $eachN = 0;
        if (is_numeric($each)) {
            $eachN = $each;
        } elseif (strlen($each) > 1) {
            if ($each[0] === 'c') {
                $eachN = round($loopStack['count'] / substr($each, 1));
            }
        } else {
            $eachN = PHP_INT_MAX;
        }
        if (($loopStack['index'] + 1) % $eachN === 0) {
            return $splitText;
        }
        return '';
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     * @return mixed
     */
    public static function last($array, ?callable $callback = null, $default = null)
    {
        if (\is_null($callback)) {
            return empty($array) ? static::value($default) : \end($array);
        }
        return static::first(\array_reverse($array), $callback, $default);
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     * @return mixed
     */
    public static function first($array, ?callable $callback = null, $default = null)
    {
        if (\is_null($callback)) {
            return empty($array) ? static::value($default) : \reset($array);
        }
        foreach ($array as $key => $value) {
            if ($callback($key, $value)) {
                return $value;
            }
        }
        return static::value($default);
    }

    /**
     * @param string $name
     * @param        $args []
     * @return string
     * @throws BadMethodCallException
     */
    public function __call($name, $args)
    {
        if ($name === 'if') {
            return $this->register_if_statement($args[0] ?? null, $args[1] ?? null);
        }
        $this->show_error('call', "function $name is not defined<br>", true, true);
        return '';
    }

    /**
     * Register an "if" statement directive.
     *
     * @param string   $name
     * @param callable $callback
     * @return string
     */
    public function register_if_statement($name, callable $callback): string
    {
        $this->conditions[$name] = $callback;
        $this->directive($name, function($expression) use ($name) {
            $tmp = $this->strip_parentheses($expression);
            return $expression !== ''
                ? $this->php_tag . " if (\$this->check('$name', $tmp)): ?>"
                : $this->php_tag . " if (\$this->check('$name')): ?>";
        });
        $this->directive('else' . $name, function($expression) use ($name) {
            $tmp = $this->strip_parentheses($expression);
            return $expression !== ''
                ? $this->php_tag . " elseif (\$this->check('$name', $tmp)): ?>"
                : $this->php_tag . " elseif (\$this->check('$name')): ?>";
        });
        $this->directive('end' . $name, function() {
            return $this->php_tag . ' endif; ?>';
        });
        return '';
    }

    /**
     * Check the result of a condition.
     *
     * @param string $name
     * @param array  $parameters
     * @return bool
     */
    public function check($name, ...$parameters): bool
    {
        return \call_user_func($this->conditions[$name], ...$parameters);
    }

    /**
     * @param bool   $bool
     * @param string $view  name of the view
     * @param array  $value arrays of values
     * @return string
     * @throws Exception
     */
    public function include_when($bool = false, $view = '', $value = []): string
    {
        if ($bool) {
            return $this->run_child($view, $value);
        }
        return '';
    }

    /**
     * Macro of function run. Runchild backups the operations, so it is ideal to run as a child process without
     * intervining with other processes.
     *
     * @param       $view
     * @param array $variables
     * @return string
     * @throws Exception
     */
    public function run_child($view, $variables = []): string
    {
        if (\is_array($variables)) {
            if ($this->include_scope) {
                $backup = $this->variables;
            } else {
                $backup = null;
            }
            $newVariables = \array_merge($this->variables, $variables);
            $backupControlStack = $this->control_stack;
            $backupSectionStack = $this->section_stack;
            $backupLookStack = $this->loops_stack;
        } else {
            $this->show_error('run/include', "RunChild: Include/run variables should be defined as array ['idx'=>'value']", true);
            return '';
        }
        $r = $this->run_internal($view, $newVariables, false, $this->is_run_fast);
        if ($backup !== null) {
            $this->variables = $backup;
        }
        $this->control_stack = $backupControlStack;
        $this->section_stack = $backupSectionStack;
        $this->loops_stack = $backupLookStack;
        return $r;
    }

    /**
     * run the blade engine. It returns the result of the code.
     *
     * @param string $view
     * @param array  $variables
     * @param bool   $forced  if true then it recompiles no matter if the compiled file exists or not.
     * @param bool   $runFast if true then the code is not compiled neither checked, and it runs directly the compiled
     *                        version.
     * @return string
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    protected function run_internal(string $view, $variables = [], $forced = false, $runFast = false): string
    {
        $this->current_view = $view;
        if (@\count($this->composer_stack)) {
            $this->eval_composer($view);
        }
        if (@\count($this->variables_global) > 0) {
            $this->variables = \array_merge($variables, $this->variables_global);
            //$this->variables_global = []; // used so we delete it.
        } else {
            $this->variables = $variables;
        }
        if (!$runFast) {
            // a) if the "compile" is forced then we compile the original file, then save the file.
            // b) if the "compile" is not forced then we read the datetime of both file, and we compared.
            // c) in both cases, if the compiled doesn't exist then we compile.
            if ($view) {
                $this->file_name = $view;
            }
            $result = $this->compile($view, $forced);
            if (!$this->is_compiled) {
                return $this->post_run($this->evaluate_text($result, $this->variables));
            }
        } elseif ($view) {
            $this->file_name = $view;
        }
        $this->is_run_fast = $runFast;
        return $this->post_run($this->evaluate_path($this->get_compiled_file(), $this->variables));
    }

    protected function eval_composer($view): void
    {
        foreach ($this->composer_stack as $viewKey => $fn) {
            if ($this->wild_card_comparison($view, $viewKey)) {
                if (is_callable($fn)) {
                    $fn($this);
                } elseif ($this->method_exists_static($fn, 'composer')) {
                    // if the method exists statically then $fn is the class and 'composer' is the name of the method
                    $fn::composer($this);
                } elseif (is_object($fn) || class_exists($fn)) {
                    // if $fn is an object, or it is a class and the class exists.
                    $instance = (is_object($fn)) ? $fn : new $fn();
                    if (method_exists($instance, 'composer')) {
                        // and the method exists inside the instance.
                        $instance->composer($this);
                    } else {
                        if ($this->mode === self::MODE_DEBUG) {
                            $this->show_error('eval_composer', "Compiler: composer() added an incorrect method [$fn]", true, true);
                            return;
                        }
                        $this->show_error('eval_composer', 'Compiler: composer() added an incorrect method', true, true);
                        return;
                    }
                } else {
                    $this->show_error('eval_composer', 'Compiler: composer() added an incorrect method', true, true);
                }
            }
        }
    }

    /**
     * It compares with wildcards (*) and returns true if both strings are equals<br>
     * The wildcards only works at the beginning and/or at the end of the string.<br>
     * **Example:**<br>
     * ```
     * Text::wild_card_comparison('abcdef','abc*'); // true
     * Text::wild_card_comparison('abcdef','*def'); // true
     * Text::wild_card_comparison('abcdef','*abc*'); // true
     * Text::wild_card_comparison('abcdef','*cde*'); // true
     * Text::wild_card_comparison('abcdef','*cde'); // false
     *
     * ```
     *
     * @param string      $text
     * @param string|null $textWithWildcard
     *
     * @return bool
     */
    protected function wild_card_comparison($text, $textWithWildcard): bool
    {
        if (($textWithWildcard === null || $textWithWildcard === '')
            || strpos($textWithWildcard, '*') === false
        ) {
            // if the text with wildcard is null or empty, or it contains two ** or it contains no * then..
            return $text == $textWithWildcard;
        }
        if ($textWithWildcard === '*' || $textWithWildcard === '**') {
            return true;
        }
        $c0 = $textWithWildcard[0];
        $c1 = substr($textWithWildcard, -1);
        $textWithWildcardClean = str_replace('*', '', $textWithWildcard);
        $p0 = strpos($text, $textWithWildcardClean);
        if ($p0 === false) {
            // no matches.
            return false;
        }
        if ($c0 === '*' && $c1 === '*') {
            // $textWithWildcard='*asasasas*'
            return true;
        }
        if ($c1 === '*') {
            // $textWithWildcard='asasasas*'
            return $p0 === 0;
        }
        // $textWithWildcard='*asasasas'
        $len = strlen($textWithWildcardClean);
        return (substr($text, -$len) === $textWithWildcardClean);
    }

    protected function method_exists_static($class, $method): bool
    {
        try {
            return (new \ReflectionMethod($class, $method))->isStatic();
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * Compile the view at the given path.
     *
     * @param string $templateName The name of the template. Example folder.template
     * @param bool   $forced       If the compilation will be forced (always compile) or not.
     * @return boolean|string True if the operation was correct, or false (if not exception)
     *                             if it fails. It returns a string (the content compiled) if is_compiled=false
     * @throws Exception
     */
    public function compile($templateName = null, $forced = false)
    {
        $compiled = $this->get_compiled_file($templateName);
        $template = $this->get_template_file($templateName);
        if (!$this->is_compiled) {
            $contents = $this->compile_string($this->get_file($template));
            $this->compile_callbacks($contents, $templateName);
            return $contents;
        }
        if ($forced || $this->is_expired($templateName)) {
            // compile the original file
            $contents = $this->compile_string($this->get_file($template));
            $this->compile_callbacks($contents, $templateName);
            if ($this->optimize) {
                // removes space and tabs and replaces by a single space
                $contents = \preg_replace('/^ {2,}/m', ' ', $contents);
                $contents = \preg_replace('/^\t{2,}/m', ' ', $contents);
            }
            $ok = @\file_put_contents($compiled, $contents);
            if ($ok === false) {
                $this->show_error(
                    'Compiling',
                    "Unable to save the file [$compiled]. Check the compile folder is defined and has the right permission"
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Get the full path of the compiled file.
     *
     * @param string $templateName
     * @return string
     */
    public function get_compiled_file($templateName = ''): string
    {
        $templateName = (empty($templateName)) ? $this->file_name : $templateName;
        $fullPath = $this->get_template_file($templateName);
        if ($fullPath == '') {
            throw new \RuntimeException('Template not found: ' . ($this->mode == self::MODE_DEBUG ? $this->template_path[0] . '/' . $templateName : $templateName));
        }
        $style = $this->compile_typefilename;
        if ($style === 'auto') {
            $style = 'sha1';
        }
        $hash = $style === 'md5' ? \md5($fullPath) : \sha1($fullPath);
        return $this->compile_dpath . '/' . basename($templateName) . '_' . $hash . $this->compile_extension;
    }

    /**
     * Get the mode of the engine.See Compiler::MODE_* constants
     *
     * @return int=[self::MODE_AUTO,self::MODE_DEBUG,self::MODE_FAST,self::MODE_SLOW][$i]
     */
    public function get_mode(): int
    {
        if (\defined('BLADEONE_MODE')) {
            $this->mode = BLADEONE_MODE;
        }
        return $this->mode;
    }

    /**
     * Set the compile mode<br>
     *
     * @param $mode int=[self::MODE_AUTO,self::MODE_DEBUG,self::MODE_FAST,self::MODE_SLOW][$i]
     * @return void
     */
    public function set_mode($mode): void
    {
        $this->mode = $mode;
    }

    /**
     * It sets the comment mode<br>
     * @param int $comment_mode =[0,1,2][$i] <br>
     *                         **0** comments are generated as php code.<br>
     *                         **1** comments are generated as html code<br>
     *                         **2** comments are ignored (no code is generated)<br>
     * @return void
     */
    public function set_comment_mode(int $comment_mode): void
    {
        $this->comment_mode = $comment_mode;
    }

    /**
     * Get the full path of the template file.
     * <p>Example: get_template_file('.abc.def')</p>
     *
     * @param string $templateName template name. If not template is set then it uses the base template.
     * @return string
     */
    public function get_template_file($templateName = ''): string
    {
        $templateName = (empty($templateName)) ? $this->file_name : $templateName;
        $namespace = '';
        $name = $templateName;
        $sep = \strpos($templateName, '::');
        if ($sep !== false) {
            $namespace = \substr($templateName, 0, $sep);
            $name = \substr($templateName, $sep + 2);
        }
        if (\strpos($name, '/') !== false) {
            return $this->locate_template($name, $namespace); // it's a literal
        }
        $arr = \explode('.', $name);
        $c = \count($arr);
        if ($c == 1) {
            // it's in the root of the template folder.
            return $this->locate_template($name . $this->file_extension, $namespace);
        }
        $file = $arr[$c - 1];
        \array_splice($arr, $c - 1, $c - 1); // delete the last element
        $path = \implode('/', $arr);
        return $this->locate_template($path . '/' . $file . $this->file_extension, $namespace);
    }

    /**
     * Find template file with the given name in all template paths in the order the paths were written
     *
     * @param string $name Filename of the template (without path)
     * @param string $namespace View/component namespace (`webkernel`), empty for default
     * @return string template file
     */
    protected function locate_template($name, $namespace = ''): string
    {
        $this->not_found_path = '';
        foreach ($this->template_dirs((string) $namespace) as $dir) {
            $path = $dir . '/' . $name;
            if (\is_file($path)) {
                return $path;
            }
            $this->not_found_path .= $path . ",";
        }
        return '';
    }

    /**
     * @return list<string>
     */
    private function template_dirs(string $namespace): array
    {
        if ($namespace === '') {
            return $this->template_path;
        }
        $dirs = $this->view_namespaces[$namespace] ?? [];
        foreach ($this->component_namespaces[$namespace] ?? [] as $dir) {
            if (! \in_array($dir, $dirs, true)) {
                $dirs[] = $dir;
            }
        }
        return $dirs;
    }

    /**
     * Get the contents of a file.
     *
     * @param string $fullFileName It gets the content of a filename or returns ''.
     *
     * @return string
     */
    public function get_file($fullFileName): string
    {
        if (\is_file($fullFileName)) {
            return \file_get_contents($fullFileName);
        }
        $this->show_error('get_file', "File does not exist at paths (separated by comma) [$this->not_found_path] or permission denied");
        return '';
    }

    protected function compile_callbacks(&$contents, $templateName): void
    {
        if (!empty($this->compile_callbacks)) {
            foreach ($this->compile_callbacks as $callback) {
                if (is_callable($callback)) {
                    $callback($contents, $templateName);
                }
            }
        }
    }

    /**
     * Determine if the view has expired.
     *
     * @param string|null $file_name
     * @return bool
     */
    public function is_expired($file_name): bool
    {
        $compiled = $this->get_compiled_file($file_name);
        $template = $this->get_template_file($file_name);
        if (!\is_file($template)) {
            if ($this->mode == self::MODE_DEBUG) {
                $this->show_error('Read file', 'Template not found :' . $this->file_name . " on file: $template", true);
            } else {
                $this->show_error('Read file', 'Template not found :' . $this->file_name, true);
            }
        }
        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (!$this->compile_dpath || !\is_file($compiled)) {
            return true;
        }
        return \filemtime($compiled) < \filemtime($template);
    }

    /**
     * Evaluates a text (string) using the current variables
     *
     * @param string $content
     * @param array  $variables
     * @return string
     * @throws Exception
     */
    protected function evaluate_text($content, $variables): string
    {
        \ob_start();
        \extract($variables);
        // We'll evaluate the contents of the view inside a try/catch block, so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            eval(' ?>' . $content . $this->php_tag);
        } catch (\Throwable $e) {
            $this->handle_view_exception($e);
        }
        return \ltrim(\ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param Exception $e
     * @return void
     * @throws $e
     */
    protected function handle_view_exception($e): void
    {
        \ob_get_clean();
        throw $e;
    }

    /**
     * Evaluates a compiled file using the current variables
     *
     * @param string $compiledFile full path of the compile file.
     * @param array  $variables
     * @return string
     * @throws Exception
     */
    protected function evaluate_path($compiledFile, $variables): string
    {
        \ob_start();
        // note, the variables are extracted locally inside this method,
        // they are not global variables :-3
        \extract($variables);
        // We'll evaluate the contents of the view inside a try/catch block, so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            include $compiledFile;
        } catch (\Throwable $e) {
            $this->handle_view_exception($e);
        }
        return \ltrim(\ob_get_clean());
    }

    /**
     * @param array $views array of views
     * @param array $value
     * @return string
     * @throws Exception
     */
    public function include_first($views = [], $value = []): string
    {
        foreach ($views as $view) {
            if ($this->template_exist($view)) {
                return $this->run_child($view, $value);
            }
        }
        return '';
    }

    /**
     * Returns true if the template exists. Otherwise, it returns false
     *
     * @param $templateName
     * @return bool
     */
    protected function template_exist($templateName): bool
    {
        $file = $this->get_template_file($templateName);
        return \is_file($file);
    }

    /**
     * Convert an array such as ["class1"=>"myclass","style="mystyle"] to class1='myclass' style='mystyle' string
     *
     * @param array|string $array array to convert
     * @return string
     */
    public function convert_arg($array): string
    {
        if (!\is_array($array)) {
            return $array;  // nothing to convert.
        }
        return \implode(' ', \array_map(self::convert_arg_call_back(...), \array_keys($array), $array));
    }

    /**
     * Returns the current token. if there is not a token then it generates a new one.
     * It could require an open session.
     *
     * @param bool   $fullToken It returns a token with the current ip.
     * @param string $tokenId   [optional] Name of the token.
     *
     * @return string
     */
    public function get_csrf_token($fullToken = false, $tokenId = '_token'): string
    {
        if ($this->csrf_token == '') {
            $this->regenerate_token($tokenId);
        }
        if ($fullToken) {
            return $this->csrf_token . '|' . $this->ip_client();
        }
        return $this->csrf_token;
    }

    /**
     * Regenerates the csrf token and stores in the session.
     * It requires an open session.
     *
     * @param string $tokenId [optional] Name of the token.
     */
    public function regenerate_token($tokenId = '_token'): void
    {
        try {
            $this->csrf_token = \bin2hex(\random_bytes(10));
        } catch (\Throwable $e) {
            $this->csrf_token = '123456789012345678901234567890'; // unable to generates a random token.
        }
        @$_SESSION[$tokenId] = $this->csrf_token . '|' . $this->ip_client();
    }

    public function ip_client()
    {
        if (
            isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && \preg_match('/^(d{1,3}).(d{1,3}).(d{1,3}).(d{1,3})$/', $_SERVER['HTTP_X_FORWARDED_FOR'])
        ) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * Validates if the csrf token is valid or not.<br>
     * It requires an open session.
     *
     * @param bool   $alwaysRegenerate [optional] Default is false.<br>
     *                                 If **true** then it will generate a new token regardless
     *                                 of the method.<br>
     *                                 If **false**, then it will generate only if the method is POST.<br>
     *                                 Note: You must not use true if you want to use csrf with AJAX.
     *
     * @param string $tokenId          [optional] Name of the token.
     *
     * @return bool It returns true if the token is valid, or it is generated. Otherwise, false.
     */
    public function csrf_is_valid($alwaysRegenerate = false, $tokenId = '_token'): bool
    {
        if (@$_SERVER['REQUEST_METHOD'] === 'POST' && $alwaysRegenerate === false) {
            $this->csrf_token = $_POST[$tokenId] ?? null; // ping pong the token.
            return $this->csrf_token . '|' . $this->ip_client() === ($_SESSION[$tokenId] ?? null);
        }
        if ($this->csrf_token == '' || $alwaysRegenerate) {
            // if not token then we generate a new one
            $this->regenerate_token($tokenId);
        }
        return true;
    }

    /**
     * Stop injecting content into a section and return its contents.
     *
     * @return string
     */
    public function yield_section(): ?string
    {
        $sc = $this->stop_section();
        return $this->sections[$sc] ?? null;
    }

    /**
     * Stop injecting content into a section.
     *
     * @param bool $overwrite
     * @return string
     */
    public function stop_section($overwrite = false): string
    {
        if (empty($this->section_stack)) {
            $this->show_error('stop_section', 'Cannot end a section without first starting one.', true, true);
        }
        $last = \array_pop($this->section_stack);
        if ($overwrite) {
            $this->sections[$last] = \ob_get_clean();
        } else {
            $this->extend_section($last, \ob_get_clean());
        }
        return $last;
    }

    /**
     * Append content to a given section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    protected function extend_section($section, $content): void
    {
        if (isset($this->sections[$section])) {
            $content = \str_replace($this->PARENTKEY, $content, $this->sections[$section]);
        }
        $this->sections[$section] = $content;
    }

    /**
     * @param mixed $object
     * @param bool  $jsconsole
     * @return void
     * @throws \JsonException
     */
    public function dump($object, bool $jsconsole = false): void
    {
        if (!$jsconsole) {
            echo '<pre>';
            \var_dump($object);
            echo '</pre>';
        } else {
            /** @noinspection BadExpressionStatementJS */
            /** @noinspection JSVoidFunctionReturnValueUsed */
            echo '<script>console.log(' . \json_encode($object, JSON_THROW_ON_ERROR) . ')</script>';
        }
    }

    /**
     * Start injecting content into a section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    public function start_section($section, $content = ''): void
    {
        if ($content === '') {
            \ob_start() && $this->section_stack[] = $section;
        } else {
            $this->extend_section($section, $content);
        }
    }

    /**
     * Stop injecting content into a section and append it.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function append_section(): string
    {
        if (empty($this->section_stack)) {
            $this->show_error('append_section', 'Cannot end a section without first starting one.', true, true);
        }
        $last = \array_pop($this->section_stack);
        if (isset($this->sections[$last])) {
            $this->sections[$last] .= \ob_get_clean();
        } else {
            $this->sections[$last] = \ob_get_clean();
        }
        return $last;
    }

    /**
     * Adds a global variable. If **$varname** is an array then it merges all the values.
     * **Example:**
     * ```
     * $this->share('variable',10.5);
     * $this->share('variable2','hello');
     * // or we could add the two variables as:
     * $this->share(['variable'=>10.5,'variable2'=>'hello']);
     * ```
     *
     * @param string|array $varname It is the name of the variable or, it is an associative array
     * @param mixed        $value
     * @return $this
     * @see Compiler::share
     */
    public function with($varname, $value = null): Compiler
    {
        return $this->share($varname, $value);
    }

    /**
     * Adds a global variable. If **$varname** is an array then it merges all the values.
     * **Example:**
     * ```
     * $this->share('variable',10.5);
     * $this->share('variable2','hello');
     * // or we could add the two variables as:
     * $this->share(['variable'=>10.5,'variable2'=>'hello']);
     * ```
     *
     * @param string|array $varname It is the name of the variable, or it is an associative array
     * @param mixed        $value
     * @return $this
     */
    public function share($varname, $value = null): Compiler
    {
        if (is_array($varname)) {
            $this->variables_global = \array_merge($this->variables_global, $varname);
        } else {
            $this->variables_global[$varname] = $value;
        }
        return $this;
    }

    /**
     * Get the string contents of a section.
     *
     * @param string $section
     * @param string $default
     * @return string
     */
    public function yield_content($section, $default = ''): string
    {
        if (isset($this->sections[$section])) {
            return \str_replace($this->PARENTKEY, $default, $this->sections[$section]);
        }
        return $default;
    }

    /**
     * Register a custom Blade compiler.
     *
     * @param callable $compiler
     * @return void
     */
    public function extend(callable $compiler): void
    {
        $this->extensions[] = $compiler;
    }

    /**
     * Register a handler for custom directives for run at runtime
     *
     * @param string   $name
     * @param callable $handler
     * @return void
     */
    public function directive_rt($name, callable $handler): void
    {
        $this->custom_directives[$name] = $handler;
        $this->custom_directives_rt[$name] = true;
    }

    /**
     * Sets the escaped content tags used for the compiler.
     *
     * @param string $openTag
     * @param string $closeTag
     * @return void
     */
    public function set_escaped_content_tags($openTag, $closeTag): void
    {
        $this->set_content_tags($openTag, $closeTag, true);
    }

    /**
     * Gets the content tags used for the compiler.
     *
     * @return array
     */
    public function get_content_tags(): array
    {
        return $this->get_tags();
    }

    /**
     * Sets the content tags used for the compiler.
     *
     * @param string $openTag
     * @param string $closeTag
     * @param bool   $escaped
     * @return void
     */
    public function set_content_tags($openTag, $closeTag, $escaped = false): void
    {
        $property = ($escaped === true) ? 'escaped_tags' : 'content_tags';
        $this->{$property} = [\preg_quote($openTag), \preg_quote($closeTag)];
    }

    /**
     * Gets the tags used for the compiler.
     *
     * @param bool $escaped
     * @return array
     */
    protected function get_tags($escaped = false): array
    {
        $tags = $escaped ? $this->escaped_tags : $this->content_tags;
        return \array_map('stripcslashes', $tags);
    }

    /**
     * Gets the escaped content tags used for the compiler.
     *
     * @return array
     */
    public function get_escaped_content_tags(): array
    {
        return $this->get_tags(true);
    }

    /**
     * Sets the function used for resolving classes with inject.
     *
     * @param callable $function
     */
    public function set_inject_resolver(callable $function): void
    {
        $this->inject_resolver = $function;
    }

    /**
     * Get the file extension for template files.
     *
     * @return string
     */
    public function get_file_extension(): string
    {
        return $this->file_extension;
    }

    /**
     * Set the file extension for the template files.
     * It must include the leading dot e.g. ".view.php"
     *
     * @param string $file_extension Example: .prefix.ext
     */
    public function set_file_extension($file_extension): void
    {
        $this->file_extension = $file_extension;
    }

    /**
     * Get the file extension for template files.
     *
     * @return string
     */
    public function get_compiled_extension(): string
    {
        return $this->compile_extension;
    }

    /**
     * Set the file extension for the compiled files.
     * Including the leading dot for the extension is required, e.g. ".view.php.compiled"
     *
     * @param $file_extension
     */
    public function set_compiled_extension($file_extension): void
    {
        $this->compile_extension = $file_extension;
    }

    /**
     * @return string
     * @see Compiler::set_compile_type_file_name
     */
    public function get_compile_type_file_name(): string
    {
        return $this->compile_typefilename;
    }

    /**
     * It determines how the compiled filename will be called.<br>
     * * **auto** (default mode) the mode is "sha1"<br>
     * * **sha1** the filename is converted into a sha1 hash (it's the slow method, but it is safest)<br>
     * * **md5** the filename is converted into a md5 hash (it's faster than sha1, and it uses less space)<br>
     * @param string $compile_typefilename =['auto','sha1','md5'][$i]
     * @return Compiler
     */
    public function set_compile_type_file_name(string $compile_typefilename): Compiler
    {
        $this->compile_typefilename = $compile_typefilename;
        return $this;
    }

    /**
     * Add new loop to the stack.
     *
     * @param array|Countable $data
     * @return void
     */
    public function add_loop($data): void
    {
        $length = \is_countable($data) || $data instanceof Countable ? \count($data) : null;
        $parent = static::last($this->loops_stack);
        $this->loops_stack[] = [
            'index' => -1,
            'iteration' => 0,
            'remaining' => isset($length) ? $length + 1 : null,
            'count' => $length,
            'first' => true,
            'even' => true,
            'odd' => false,
            'last' => isset($length) ? $length == 1 : null,
            'depth' => \count($this->loops_stack) + 1,
            'parent' => $parent ? (object)$parent : null,
        ];
    }

    /**
     * Increment the top loop's indices.
     *
     * @return object
     */
    public function increment_loop_indices(): object
    {
        $c = \count($this->loops_stack) - 1;
        $loop = &$this->loops_stack[$c];
        $loop['index']++;
        $loop['iteration']++;
        $loop['first'] = $loop['index'] == 0;
        $loop['even'] = $loop['index'] % 2 == 0;
        $loop['odd'] = !$loop['even'];
        if (isset($loop['count'])) {
            $loop['remaining']--;
            $loop['last'] = $loop['index'] == $loop['count'] - 1;
        }
        return (object)$loop;
    }

    /**
     * Pop a loop from the top of the loop stack.
     *
     * @return void
     */
    public function pop_loop(): void
    {
        \array_pop($this->loops_stack);
    }

    /**
     * Get an instance of the first loop in the stack.
     *
     * @return object|null
     */
    public function get_first_loop(): ?object
    {
        return ($last = static::last($this->loops_stack)) ? (object)$last : null;
    }

    /**
     * Get the rendered contents of a partial from a loop.
     *
     * @param string $view
     * @param array  $data
     * @param string $iterator
     * @param string $empty
     * @return string
     * @throws Exception
     */
    public function render_each($view, $data, $iterator, $empty = 'raw|'): string
    {
        $result = '';
        if (\count($data) > 0) {
            // If is actually data in the array, we will loop through the data and append
            // an instance of the partial view to the final result HTML passing in the
            // iterated value of this data array, allowing the views to access them.
            foreach ($data as $key => $value) {
                $data = ['key' => $key, $iterator => $value];
                $result .= $this->run_child($view, $data);
            }
        } elseif (static::starts_with($empty, 'raw|')) {
            $result = \substr($empty, 4);
        } else {
            $result = $this->run($empty);
        }
        return $result;
    }

    /**
     * Run the blade engine. It returns the result of the code.
     *
     * @param string|null $view      The name of the cache. Ex: "folder.folder.view" ("/folder/folder/view.view.php")
     * @param array       $variables An associative arrays with the values to display.
     * @return string
     * @throws Exception
     */
    public function run($view = null, $variables = []): string
    {
        $mode = $this->get_mode();
        if ($view === null) {
            $view = $this->view_stack;
        }
        $this->view_stack = null;
        if ($view === null) {
            $this->show_error('run', 'Compiler: view not set', true);
            return '';
        }
        $forced = ($mode & 1) !== 0; // mode=1 forced:it recompiles no matter if the compiled file exists or not.
        $runFast = ($mode & 2) !== 0; // mode=2 runfast: the code is not compiled neither checked, and it runs directly the compiled
        $this->sections = [];
        if ($mode == 3) {
            $this->show_error('run', "we can't force and run fast at the same time", true);
        }
        return $this->run_internal($view, $variables, $forced, $runFast);
    }

    /**
     * It executes a post run execution. It is used to display the stacks.
     * @noinspection PhpVariableIsUsedOnlyInClosureInspection
     */
    protected function post_run(?string $string)
    {
        if (!$string) {
            return $string;
        }
        if (strpos($string, $this->escape_stack0) === false) {
            // nothing to post run
            return $string;
        }
        $me = $this;
        // we returned the escape character.
        return preg_replace_callback('/' . $this->escape_stack0 . '\s?([A-Za-z0-9_:() ,*.@$]+)\s?' . $this->escape_stack1 . '/u',
            static function($matches) use ($me) {
                $l0 = strlen($me->escape_stack0);
                $l1 = strlen($me->escape_stack1);
                $item = trim(is_array($matches) ? substr($matches[0], $l0, -$l1) : substr($matches, $l0, -$l1));
                $items = explode(',', $item);
                return $me->yield_push_content($items[0], $items[1] ?? null);
                //return is_array($r) ? $flagtxt . json_encode($r) : $flagtxt . $r;
            }, $string);
    }

    /**
     * It sets the current view<br>
     * This value is cleared when it is used (method run).<br>
     * **Example:**<br>
     * ```
     * $this->set_view('folder.view')->share(['var1'=>20])->run(); // or $this->run('folder.view',['var1'=>20]);
     * ```
     *
     * @param string $view
     * @return Compiler
     */
    public function set_view($view): Compiler
    {
        $this->view_stack = $view;
        return $this;
    }

    /**
     * It injects a function, an instance, or a method class when a view is called.<br>
     * It could be stacked.   If it sets null then it clears all definitions.
     * **Example:**<br>
     * ```
     * $this->composer('folder.view',function($bladeOne) { $bladeOne->share('newvalue','hi there'); });
     * $this->composer('folder.view','namespace1\namespace2\SomeClass'); // SomeClass must exist, and it must have the
     *                                                                   // method 'composer'
     * $this->composer('folder.*',$instance); // $instance must have the method called 'composer'
     * $this->composer(); // clear all composer.
     * ```
     *
     * @param string|array|null    $view It could contain wildcards (*). Example:
     *                                   'aa.bb.cc','*.bb.cc','aa.bb.*','*.bb.*'
     *
     * @param callable|string|null $functionOrClass
     * @return Compiler
     */
    public function composer($view = null, $functionOrClass = null): Compiler
    {
        if ($view === null && $functionOrClass === null) {
            $this->composer_stack = [];
            return $this;
        }
        if (is_array($view)) {
            foreach ($view as $v) {
                $this->composer_stack[$v] = $functionOrClass;
            }
        } else {
            $this->composer_stack[$view] = $functionOrClass;
        }
        return $this;
    }

    /**
     * Start a component rendering process.
     *
     * @param string $name
     * @param array  $data
     * @return void
     */
    public function start_component($name, array $data = []): void
    {
        if (\ob_start()) {
            $this->component_stack[] = $name;
            $this->component_data[$this->current_component()] = $data;
            $this->slots[$this->current_component()] = [];
        }
    }

    /**
     * Get the index for the current component.
     *
     * @return int
     */
    protected function current_component(): int
    {
        return \count($this->component_stack) - 1;
    }

    /**
     * Render the current component.
     *
     * @return string
     * @throws Exception
     */
    public function render_component(): string
    {
        //echo "<hr>render<br>";
        $name = \array_pop($this->component_stack);
        //return $this->run_child($name, $this->component_data());
        $cd = $this->component_data();
        $clean = array_keys($cd);
        $r = $this->run_child($name, $cd);
        // we clean variables defined inside the component (so they are garbaged when the component is used)
        foreach ($clean as $key) {
            unset($this->variables[$key]);
        }
        return $r;
    }

    /**
     * Get the data for the given component.
     *
     * @return array
     */
    protected function component_data(): array
    {
        $cs = count($this->component_stack);
        //echo "<hr>";
        //echo "<br>data:<br>";
        //var_dump($this->component_data);
        //echo "<br>datac:<br>";
        //var_dump(count($this->component_stack));
        return array_merge(
            $this->component_data[$cs],
            ['slot' => trim(ob_get_clean())],
            $this->slots[$cs]
        );
    }

    /**
     * Start the slot rendering process.
     *
     * @param string      $name
     * @param string|null $content
     * @return void
     */
    public function slot($name, $content = null): void
    {
        if (\count(\func_get_args()) === 2) {
            $this->slots[$this->current_component()][$name] = $content;
        } elseif (\ob_start()) {
            $this->slots[$this->current_component()][$name] = '';
            $this->slot_stack[$this->current_component()][] = $name;
        }
    }

    /**
     * Save the slot content for rendering.
     *
     * @return void
     */
    public function end_slot(): void
    {
        static::last($this->component_stack);
        $currentSlot = \array_pop(
            $this->slot_stack[$this->current_component()]
        );
        $this->slots[$this->current_component()][$currentSlot] = \trim(\ob_get_clean());
    }

    /**
     * @return string
     */
    public function get_php_tag(): string
    {
        return $this->php_tag;
    }

    /**
     * @param string $php_tag
     */
    public function set_php_tag($php_tag): void
    {
        $this->php_tag = $php_tag;
    }

    /**
     * @return string
     */
    public function get_current_user(): string
    {
        return $this->current_user;
    }

    /**
     * @param string $current_user
     */
    public function set_current_user($current_user): void
    {
        $this->current_user = $current_user;
    }

    /**
     * @return string
     */
    public function get_current_role(): string
    {
        return $this->current_role;
    }

    /**
     * @param string $current_role
     */
    public function set_current_role($current_role): void
    {
        $this->current_role = $current_role;
    }

    /**
     * @return string[]
     */
    public function get_current_permission(): array
    {
        return $this->current_permission;
    }

    /**
     * @param string[] $current_permission
     */
    public function set_current_permission($current_permission): void
    {
        $this->current_permission = $current_permission;
    }

    /**
     * Returns the current base url without trailing slash.
     *
     * @return string
     */
    public function get_base_url(): string
    {
        return $this->base_url;
    }

    /**
     * It sets the base url and, it also calculates the relative path.<br>
     * The base url defines the "root" of the project, not always the level of the domain, but it could be
     * any folder.<br>
     * This value is used to calculate the relativity of the resources, but it is also used to set the domain.<br>
     * **Note:** The trailing slash is removed automatically if it's present.<br>
     * **Note:** We should not use arguments or name of the script.<br>
     * **Examples:**<br>
     * ```
     * $this->set_base_url('http://domain.dom/myblog');
     * $this->set_base_url('http://domain.dom/corporate/erp');
     * $this->set_base_url('http://domain.dom/blog.php?args=20'); // avoid this one.
     * $this->set_base_url('http://another.dom');
     * ```
     *
     * @param string $base_url Example http://www.web.com/folder  https://www.web.com/folder/anotherfolder
     * @return Compiler
     */
    public function set_base_url(string $base_url): Compiler
    {
        $this->base_url = \rtrim($base_url, '/'); // base with the url trimmed
        $this->base_domain = @parse_url($this->base_url)['host'];
        $current_url = $this->get_current_url_calculated();
        if ($current_url === '') {
            $this->relative_path = '';
            return $this;
        }
        if (\strpos($current_url, $this->base_url) === 0) {
            $part = \str_replace($this->base_url, '', $current_url);
            $numf = \substr_count($part, '/') - 1;
            $numf = ($numf > 10) ? 10 : $numf; // avoid overflow
            $this->relative_path = ($numf < 0) ? '' : \str_repeat('../', $numf);
        } else {
            $this->relative_path = '';
        }
        return $this;
    }

    /**
     * It sets a CDN Url used by @assetcdn("someresource.jpg")<br>
     * **Example:**
     * ```
     * $this->set_cdn_url('http://domain.dom/myblog');
     * ```
     *
     * @param string $cdnurl the full path url without the trailing slash
     * @return $this
     */
    public function set_cdn_url(string $cdnurl): Compiler
    {
        $this->cdn_url = $cdnurl;
        return $this;
    }

    /**
     * It gets the full current url calculated with the information sends by the user.<br>
     * **Note:** If we set baseurl, then it always uses the baseurl as domain (it's safe).<br>
     * **Note:** This information could be forged/faked by the end-user.<br>
     * **Note:** It returns empty '' if it is called in a command line interface / non-web.<br>
     * **Note:** It doesn't return the user and password.<br>
     * @param bool $noArgs if true then it excludes the arguments.
     * @return string
     */
    public function get_current_url_calculated($noArgs = false): string
    {
        if (!isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) {
            return '';
        }
        $host = $this->base_domain ?? $_SERVER['HTTP_HOST']; // <-- it could be forged!
        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
        $port = $_SERVER['SERVER_PORT'];
        $port2 = (($link === 'http' && $port === '80') || ($link === 'https' && $port === '443')) ? '' : ':' . $port;
        $link .= "://$host$port2$_SERVER[REQUEST_URI]";
        if ($noArgs) {
            $link = @explode('?', $link)[0];
        }
        return $link;
    }

    /**
     * It returns the relative path to the base url or empty if not set<br>
     * **Example:**<br>
     * ```
     * // current url='http://domain.dom/page/subpage/web.php?aaa=2
     * $this->set_base_url('http://domain.dom/');
     * $this->get_relative_path(); // '../../'
     * $this->set_base_url('http://domain.dom/');
     * $this->get_relative_path(); // '../../'
     * ```
     * **Note:**The relative path is calculated when we set the base url.
     *
     * @return string
     * @see Compiler::set_base_url
     */
    public function get_relative_path(): string
    {
        return $this->relative_path;
    }

    /**
     * It gets the full current canonical url.<br>
     * **Example:** https://www.mysite.com/aaa/bb/php.php?aa=bb
     * <ul>
     * <li>It returns the $this->canonical_url value if is not null</li>
     * <li>Otherwise, it returns the $this->current_url if not null</li>
     * <li>Otherwise, the url is calculated with the information sends by the user</li>
     * </ul>
     *
     * @return string|null
     */
    public function get_canonical_url(): ?string
    {
        return $this->canonical_url ?? $this->get_current_url();
    }

    /**
     * It sets the full canonical url.<br>
     * **Example:** https://www.mysite.com/aaa/bb/php.php?aa=bb
     *
     * @param string|null $canonUrl
     * @return Compiler
     */
    public function set_canonical_url($canonUrl = null): Compiler
    {
        $this->canonical_url = $canonUrl;
        return $this;
    }

    /**
     * It gets the full current url<br>
     * **Example:** https://www.mysite.com/aaa/bb/php.php?aa=bb
     * <ul>
     * <li>It returns the $this->current_url if not null</li>
     * <li>Otherwise, the url is calculated with the information sends by the user</li>
     * </ul>
     *
     * @param bool $noArgs if true then it ignores the arguments.
     * @return string|null
     */
    public function get_current_url($noArgs = false): ?string
    {
        $link = $this->current_url ?? $this->get_current_url_calculated();
        if ($noArgs) {
            $link = @explode('?', $link)[0];
        }
        return $link;
    }

    /**
     * It sets the full current url.<br>
     * **Example:** https://www.mysite.com/aaa/bb/php.php?aa=bb
     * **Note:** If the current url is not set, then the system could calculate the current url.
     *
     * @param string|null $current_url
     * @return Compiler
     */
    public function set_current_url($current_url = null): Compiler
    {
        $this->current_url = $current_url;
        return $this;
    }

    /**
     * If true then it optimizes the result (it removes tab and extra spaces).
     *
     * @param bool $bool
     * @return Compiler
     */
    public function set_optimize($bool = false): Compiler
    {
        $this->optimize = $bool;
        return $this;
    }

    /**
     * It sets the callback function for authentication. It is used by @can and @cannot
     *
     * @param callable $fn
     */
    public function set_can_function(callable $fn): void
    {
        $this->auth_call_back = $fn;
    }

    /**
     * It sets the callback function for authentication. It is used by @canany
     *
     * @param callable $fn
     */
    public function set_any_function(callable $fn): void
    {
        $this->auth_any_call_back = $fn;
    }

    /**
     * It sets the callback function for errors. It is used by @error
     *
     * @param callable $fn
     */
    public function set_error_function(callable $fn): void
    {
        $this->error_call_back = $fn;
    }

    //</editor-fold>
    //<editor-fold desc="push">
    /**
     * Get the entire loop stack.
     *
     * @return array
     */
    public function get_loop_stack(): array
    {
        return $this->loops_stack;
    }

    /**
     * It adds a string inside a quoted string<br>
     * **example:**<br>
     * ```
     * $this->add_inside_quote("'hello'"," world"); // 'hello world'
     * $this->add_inside_quote("hello"," world"); // hello world
     * ```
     *
     * @param $quoted
     * @param $newFragment
     * @return string
     */
    public function add_inside_quote($quoted, $newFragment): string
    {
        if ($this->is_quoted($quoted)) {
            return substr($quoted, 0, -1) . $newFragment . substr($quoted, -1);
        }
        return $quoted . $newFragment;
    }

    /**
     * Return true if the string is a php variable (it starts with $)
     *
     * @param string|null $text
     * @return bool
     */
    public function is_variable_php($text): bool
    {
        if (!$text || strlen($text) < 2) {
            return false;
        }
        return $text[0] === '$';
    }

    /**
     * It's the same as "@_e", however it parses the text (using sprintf).
     * If the operation fails then, it returns the original expression without translation.
     *
     * @param $phrase
     *
     * @return string
     */
    public function _ef($phrase): string
    {
        $argv = \func_get_args();
        $r = $this->_e($phrase);
        $argv[0] = $r; // replace the first argument with the translation.
        $result = @sprintf(...$argv);
        return !$result ? $r : $result;
    }

    /**
     * Tries to translate the word if it's in the array defined by CompilerLang::$dictionary
     * If the operation fails then, it returns the original expression without translation.
     *
     * @param $phrase
     *
     * @return string
     */
    public function _e($phrase): string
    {
        if ((!\array_key_exists($phrase, static::$dictionary))) {
            $this->missing_translation($phrase);
            return $phrase;
        }
        return static::$dictionary[$phrase];
    }

    /**
     * Log a missing translation into the file $this->missing_log.<br>
     * If the file is not defined, then it doesn't write the log.
     *
     * @param string $txt Message to write on.
     */
    protected function missing_translation($txt): void
    {
        if (!$this->missing_log) {
            return; // if there is not a file assigned then it skips saving.
        }
        $fz = @\filesize($this->missing_log);
        if (\is_object($txt) || \is_array($txt)) {
            $txt = \print_r($txt, true);
        }
        // Rewrite file if more than 100000 bytes
        $mode = ($fz > 100000) ? 'w' : 'a';
        $fp = \fopen($this->missing_log, $mode);
        \fwrite($fp, $txt . "\n");
        \fclose($fp);
    }

    /**
     * if num is more than one then it returns the phrase in plural, otherwise the phrase in singular.
     * Note: the translation should be as follows: $msg['Person']='Person' $msg=['Person']['p']='People'
     *
     * @param string $phrase
     * @param string $phrases
     * @param int    $num
     *
     * @return string
     */
    public function _n($phrase, $phrases, $num = 0): string
    {
        if ((!\array_key_exists($phrase, static::$dictionary))) {
            $this->missing_translation($phrase);
            return ($num <= 1) ? $phrase : $phrases;
        }
        return ($num <= 1) ? $this->_e($phrase) : $this->_e($phrases);
    }

    /**
     * @param $expression
     * @return string
     * @see Compiler::get_canonical_url
     */
    public function compile_canonical($expression = null): string
    {
        return '<link rel="canonical" href="' . $this->php_tag
            . ' echo $this->get_canonical_url();?>" />';
    }

    /**
     * @param $expression
     * @return string
     * @see Compiler::get_base_url
     */
    public function compile_base($expression = null): string
    {
        return '<base rel="canonical" href="' . $this->php_tag
            . ' echo $this->get_base_url() ;?>" />';
    }

    protected function compile_use($expression): string
    {
        return $this->php_tag . 'use ' . $this->strip_parentheses($expression) . '; ?>';
    }

    protected function compile_switch($expression): string
    {
        $this->switch_count++;
        $this->first_case_in_switch = true;
        return $this->php_tag . "switch $expression {";
    }
    //</editor-fold>
    //<editor-fold desc="compile extras">
    protected function compile_dump($expression): string
    {
        return $this->php_tag_echo . "\$this->dump$expression;?>";
    }

    protected function compile_relative($expression): string
    {
        return $this->php_tag_echo . "\$this->relative$expression;?>";
    }

    protected function compile_method($expression): string
    {
        $v = $this->strip_parentheses($expression);
        return "<input type='hidden' name='_method' value='{$this->php_tag}echo $v; " . "?>'/>";
    }

    protected function compilecsrf($expression = null): string
    {
        $expression = $expression ?? "'_token'";
        return "<input type='hidden' name='$this->php_tag echo $expression; ?>' value='{$this->php_tag}echo \$this->csrf_token; " . "?>'/>";
    }

    protected function compile_dd($expression): string
    {
        return $this->php_tag_echo . "'<pre>'; var_dump$expression; echo '</pre>';?>";
    }

    /**
     * Execute the case tag.
     *
     * @param $expression
     * @return string
     */
    protected function compile_case($expression): string
    {
        if ($this->first_case_in_switch) {
            $this->first_case_in_switch = false;
            return 'case ' . $expression . ': ?>';
        }
        return $this->php_tag . "case $expression: ?>";
    }

    /**
     * Compile the while statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_while($expression): string
    {
        return $this->php_tag . "while$expression: ?>";
    }

    /**
     * default tag used for switch/case
     *
     * @return string
     */
    protected function compile_default(): string
    {
        if ($this->first_case_in_switch) {
            return $this->show_error('@default', '@switch without any @case', true);
        }
        return $this->php_tag . 'default: ?>';
    }

    protected function compile_endswitch(): string
    {
        --$this->switch_count;
        if ($this->switch_count < 0) {
            return $this->show_error('@endswitch', 'Missing @switch', true);
        }
        return $this->php_tag . '} // end switch ?>';
    }

    /**
     * Compile while statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_inject($expression): string
    {
        $ex = $this->strip_parentheses($expression);
        $p0 = \strpos($ex, ',');
        if (!$p0) {
            $var = $this->strip_quotes($ex);
            $namespace = '';
        } else {
            $var = $this->strip_quotes(\substr($ex, 0, $p0));
            $namespace = $this->strip_quotes(\substr($ex, $p0 + 1));
        }
        return $this->php_tag . "\$$var = \$this->inject_class('$namespace', '$var'); ?>";
    }

    /**
     * Remove first and end quote from a quoted string of text
     *
     * @param mixed $text
     * @return null|string|string[]
     */
    public function strip_quotes($text)
    {
        if (!$text || strlen($text) < 2) {
            return $text;
        }
        $text = trim($text);
        $p0 = $text[0];
        $p1 = \substr($text, -1);
        if ($p0 === $p1 && ($p0 === '"' || $p0 === "'")) {
            return \substr($text, 1, -1);
        }
        return $text;
    }

    /**
     * Execute the user defined extensions.
     *
     * @param string $value
     * @return string
     */
    protected function compile_extensions($value): string
    {
        foreach ($this->extensions as $compiler) {
            $value = $compiler($value, $this);
        }
        return $value;
    }

    /**
     * Compile Blade comments into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compile_comments($value): string
    {
        $pattern = "/" . $this->content_tags[0] . "--(.*?)--" . $this->content_tags[1] . "/s";
        switch ($this->comment_mode) {
            case 0:
                return \preg_replace($pattern, $this->php_tag . '/*$1*/ ?>', $value);
            case 1:
                return \preg_replace($pattern, '<!-- $1 -->', $value);
            default:
                return \preg_replace($pattern, '', $value);
        }
    }

    /**
     * Compile Blade echos into valid PHP.
     *
     * @param string $value
     * @return string
     * @throws Exception
     */
    protected function compile_echos($value): string
    {
        foreach ($this->get_echo_methods() as $method => $length) {
            $value = $this->$method($value);
        }
        return $value;
    }

    /**
     * Get the echo methods in the proper order for compilation.
     *
     * @return array
     */
    protected function get_echo_methods(): array
    {
        $methods = [
            'compile_rawechos' => \strlen(\stripcslashes($this->raw_tags[0])),
            'compile_escapedechos' => \strlen(\stripcslashes($this->escaped_tags[0])),
            'compile_regularechos' => \strlen(\stripcslashes($this->content_tags[0])),
        ];
        \uksort($methods, static function($method1, $method2) use ($methods) {
            // Ensure the longest tags are processed first
            if ($methods[$method1] > $methods[$method2]) {
                return -1;
            }
            if ($methods[$method1] < $methods[$method2]) {
                return 1;
            }
            // Otherwise, give preference to raw tags (assuming they've overridden)
            if ($method1 === 'compile_rawechos') {
                return -1;
            }
            if ($method2 === 'compile_rawechos') {
                return 1;
            }
            if ($method1 === 'compile_escapedechos') {
                return -1;
            }
            if ($method2 === 'compile_escapedechos') {
                return 1;
            }
            throw new BadMethodCallException("Method [$method1] not defined");
        });
        return $methods;
    }

    /**
     * Compile Blade components that start with "x-".
     *
     * @param string $value
     *
     * @return array|string|string[]|null
     */
    protected function compile_components($value)
    {
        $namespaced = function ($match) {
            if (isset($match[5])) {
                $match[5] = $this->compile_components($match[5]);
            }
            $paramsCompiled = $this->parse_params($match[3] ?? '');
            $str = "('" . $match[1] . '::' . $match[2] . "'," . $paramsCompiled . ")";
            return self::compile_component($str) . ($match[5] ?? '') . self::compile_endcomponent();
        };
        $value = preg_replace_callback(
            '/<(?:x-)?([a-z0-9.-]+)::([a-z0-9.-]+)(\s[^>]*)?(>((?:(?!<\/(?:x-)?\1::\2>).)*)<\/(?:x-)?\1::\2>|\/>)/ms',
            $namespaced,
            $value
        );
        $callback = function($match) {
            if (isset($match[4]) && static::contains($match[0], 'x-')) {
                $match[4] = $this->compile_components($match[4]);
            }
            $paramsCompiled = $this->parse_params($match[2]);
            $str = "('components." . $match[1] . "'," . $paramsCompiled . ")";
            return self::compile_component($str) . ($match[4] ?? '') . self::compile_endcomponent();
        };
        return preg_replace_callback('/<x-([a-z0-9.-]+)(\s[^>]*)?(>((?:(?!<\/x-\1>).)*)<\/x-\1>|\/>)/ms', $callback, $value);
    }

    protected function parse_params($params): string
    {
        preg_match_all('/([a-zA-Z0-9:-]*?)\s*?=\s*?(.+?)(\s|$)/ms', $params, $matches);
        $paramsCompiled = [];
        foreach ($matches[1] as $i => $key) {
            $value = str_replace('"', '', $matches[2][$i]);
            //its php code
            if (self::starts_with($key, ':')) {
                $key = substr($key, 1);
                $paramsCompiled[] = '"' . $key . '"' . '=>' . $value;
                continue;
            }
            $paramsCompiled[] = '"' . $key . '"' . '=>' . '"' . $value . '"';
        }
        return '[' . implode(',', $paramsCompiled) . ']';
    }

    /**
     * Compile Blade statements that start with "@".
     *
     * @param string $value
     *
     * @return array|string|string[]|null
     */
    protected function compile_statements($value)
    {
        /**
         * @param array $match
         *                    [0]=full expression with @ and parenthesis
         *                    [1]=expression without @ and argument
         *                    [2]=????
         *                    [3]=argument with parenthesis and without the first @
         *                    [4]=argument without parenthesis.
         *
         * @return mixed|string
         */
        $callback = function($match) {
            if (static::contains($match[1], '@')) {
                // @@escaped tag
                $match[0] = isset($match[3]) ? $match[1] . $match[3] : $match[1];
            } else {
                if (strpos($match[1], '::') !== false) {
                    // Someclass::method
                    return $this->compile_statementclass($match);
                }
                if (isset($this->custom_directives_rt[$match[1]])) {
                    if ($this->custom_directives_rt[$match[1]]) {
                        $match[0] = $this->compile_statementcustom($match);
                    } else {
                        $match[0] = \call_user_func(
                            $this->custom_directives[$match[1]],
                            $this->strip_parentheses(static::get($match, 3))
                        );
                    }
                } else {
                    $nameMethod = 'compile_' . $match[1];
                    if (isset($this->methods[$nameMethod])) {
                        return $this->methods[$nameMethod](static::get($match, 3));
                    }
                    if (\method_exists($this, $nameMethod)) {
                        // it calls the function compile<name of the tag>
                        return $this->$nameMethod(static::get($match, 3));
                    }
                    $nameMethod = 'runtime_' . $match[1];
                    $m4 = $match[4] ?? '';
                    if (isset($this->methods[$nameMethod])) {
                        return $this->autoruntime($m4, $nameMethod);
                    }
                    if (\method_exists($this, $nameMethod)) {
                        return $this->autoruntime($m4, $nameMethod, true);
                    }
                    return $match[0];
                }
            }
            return isset($match[3]) ? $match[0] : $match[0] . $match[2];
        };
        /* return \preg_replace_callback('/\B@(@?\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value); */
        return preg_replace_callback('/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value);
    }

    /**
     * This function generates a php code to run a runtime method.
     * @param string|null $expression    the expression to add in the code.<br>
     *                                   For compile, it is of the type "($a2,"222")"
     *                                   For runtime, it is of the time "arg1=$a2 arg2="222""
     * @param string      $nameFunction  The name of the function.
     * @param bool        $compile_method If the method is a compiled method, or it is a runtime method.
     * @return string
     */
    protected function autoruntime(?string $expression, string $nameFunction, $compile_method = false): string
    {
        $args = $this->parse_args($expression, ' ', '=', false);
        $argsV = '[';
        foreach ($args as $k => $v) {
            $argsV .= "'$k'=>$v,";
        }
        $argsV .= ']';
        if ($compile_method) {
            return $this->wrap_php("\$this->$nameFunction($argsV)", '', false);
        }
        return $this->wrap_php("\$this->methods['$nameFunction']($argsV)", '', false);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function contains($haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '') {
                if (\function_exists('mb_strpos')) {
                    if (\mb_strpos($haystack, $needle) !== false) {
                        return true;
                    }
                } elseif (\strpos($haystack, $needle) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function compile_statementclass($match): string
    {
        if (isset($match[3])) {
            return $this->php_tag_echo . $this->fix_namespace_class($match[1]) . $match[3] . '; ?>';
        }
        return $this->php_tag_echo . $this->fix_namespace_class($match[1]) . '(); ?>';
    }

    /**
     * Util method to fix namespace of a class<br>
     * Example: "SomeClass::method()" -> "\namespace\SomeClass::method()"<br>
     *
     * @param string $text
     *
     * @return string
     * @see Compiler
     */
    protected function fix_namespace_class($text): string
    {
        if (strpos($text, '::') === false) {
            return $text;
        }
        $classPart = explode('::', $text, 2);
        if (isset($this->alias_classes[$classPart[0]])) {
            $classPart[0] = $this->alias_classes[$classPart[0]];
        }
        return $classPart[0] . '::' . $classPart[1];
    }

    /**
     * For compile custom directive at runtime.
     *
     * @param $match
     * @return string
     */
    protected function compile_statementcustom($match): string
    {
        $v = $this->strip_parentheses(static::get($match, 3));
        $v = ($v == '') ? '' : ',' . $v;
        return $this->php_tag . 'call_user_func($this->custom_directives[\'' . $match[1] . '\']' . $v . '); ?>';
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param ArrayAccess|array $array
     * @param string            $key
     * @param mixed             $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        $accesible = \is_array($array) || $array instanceof ArrayAccess;
        if (!$accesible) {
            return static::value($default);
        }
        if (\is_null($key)) {
            return $array;
        }
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        foreach (\explode('.', $key) as $segment) {
            if (static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return static::value($default);
            }
        }
        return $array;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param ArrayAccess|array $array
     * @param string|int        $key
     * @return bool
     */
    public static function exists($array, $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return \array_key_exists($key, $array);
    }

    /**
     * This method removes the parenthesis of the expression and parse the arguments.
     * @param string $expression
     * @return array
     */
    protected function get_args($expression): array
    {
        return $this->parse_args($this->strip_parentheses($expression), ' ');
    }

    /**
     * It separates a string using a separator and an identifier<br>
     * It excludes quotes,double quotes and the "¬" symbol.<br>
     * **Example**<br>
     * ```
     * $this->parse_args('a=2,b='a,b,c',d'); // ['a'=>'2','b'=>'a,b,c','d'=>null]
     * $this->parse_args('a=2,b=c,d'); // ['a'=>'2','b'=>'c','d'=>null]
     * $this->parse_args('a=2 b=c',' '); // ['a'=>'2','b'=>'c']
     * $this->parse_args('a:2 b:c',' ',':'); // ['a'=>'2','b'=>'c']
     * ```
     * Note: parse_args('a = 2 b = c',' '); with return 4 values instead of 2.
     *
     * @param string $text      the text to separate
     * @param string $separator the separator of arguments
     * @param string $assigment the character used to assign a new value
     * @param bool   $emptyKey  if the argument is without value, we return it as key (true) or value (false) ?
     * @return array
     */
    public function parse_args($text, $separator = ',', $assigment = '=', $emptyKey = true): array
    {
        if ($text === null || $text === '') {
            return []; //nothing to convert.
        }
        $chars = $text; // str_split($text);
        $parts = [];
        $nextpart = '';
        $strL = strlen($chars);
        $stringArr = '"\'¬';
        $parenthesis = '([{';
        $parenthesisClose = ')]}';
        $insidePar = false;
        for ($i = 0; $i < $strL; $i++) {
            $char = $chars[$i];
            // we check if the character is a parenthesis.
            $pp = strpos($parenthesis, $char);
            if ($pp !== false) {
                // is a parenthesis, so we mark as inside a parenthesis.
                $insidePar = $parenthesisClose[$pp];
            }
            if ($char === $insidePar) {
                // we close the parenthesis.
                $insidePar = false;
            }
            if (strpos($stringArr, $char) !== false) { // if ($char === '"' || $char === "'" || $char === "¬") {
                // we found a string initializer
                $inext = strpos($text, $char, $i + 1);
                $inext = $inext === false ? $strL : $inext;
                $nextpart .= substr($text, $i, $inext - $i + 1);
                $i = $inext;
            } else {
                $nextpart .= $char;
            }
            if ($char === $separator && !$insidePar) {
                $parts[] = substr($nextpart, 0, -1);
                $nextpart = '';
            }
        }
        if ($nextpart !== '') {
            $parts[] = $nextpart;
        }
        $result = [];
        // duct taping for key= argument (it has a space). however, it doesn't work with key =argument
        /*
        foreach ($parts as $k=>$part) {
            if(substr($part,-1)===$assigment && isset($parts[$k+1])) {
                var_dump('ok');
                $parts[$k].=$parts[$k+1];
                unset($parts[$k+1]);
            }
        }
        */
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part) {
                $char = $part[0];
                if (strpos($stringArr, $char) !== false) { // if ($char === '"' || $char === "'" || $char === "¬") {
                    if ($emptyKey) {
                        $result[$part] = null;
                    } else {
                        $result[] = $part;
                    }
                } else {
                    $r = explode($assigment, $part, 2);
                    if (count($r) === 2) {
                        // key=value.
                        $result[trim($r[0])] = trim($r[1]);
                    } elseif ($emptyKey) {
                        $result[trim($r[0])] = null;
                    } else {
                        $result[] = trim($r[0]);
                    }
                }
            }
        }
        return $result;
    }

    public function parse_args_old($text, $separator = ','): array
    {
        if ($text === null || $text === '') {
            return []; //nothing to convert.
        }
        $chars = str_split($text);
        $parts = [];
        $nextpart = '';
        $strL = count($chars);
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $strL; $i++) {
            $char = $chars[$i];
            if ($char === '"' || $char === "'") {
                $inext = strpos($text, $char, $i + 1);
                $inext = $inext === false ? $strL : $inext;
                $nextpart .= substr($text, $i, $inext - $i + 1);
                $i = $inext;
            } else {
                $nextpart .= $char;
            }
            if ($char === $separator) {
                $parts[] = substr($nextpart, 0, -1);
                $nextpart = '';
            }
        }
        if ($nextpart !== '') {
            $parts[] = $nextpart;
        }
        $result = [];
        foreach ($parts as $part) {
            $r = explode('=', $part, 2);
            $result[trim($r[0])] = count($r) === 2 ? trim($r[1]) : null;
        }
        return $result;
    }

    /**
     * Compile the "raw" echo statements.
     *
     * @param string $value
     * @return string
     */
    protected function compile_rawechos($value): string
    {
        $pattern = \sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->raw_tags[0], $this->raw_tags[1]);
        $callback = function($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];
            return $matches[1] ? \substr(
                $matches[0],
                1
            ) : $this->php_tag_echo . $this->compile_echodefaults($matches[2]) . '; ?>' . $whitespace;
        };
        return \preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the default values for the echo statement.
     * Example:
     * {{ $test or 'test2' }} compiles to {{ isset($test) ? $test : 'test2' }}
     *
     * @param string $value
     * @return string
     */
    protected function compile_echodefaults($value): string
    {
        // Source: https://www.php.net/manual/en/language.variables.basics.php
        $patternPHPVariableName = '\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*';
        $result = \preg_replace('/^(' . $patternPHPVariableName . ')\s+or\s+(.+?)$/s', 'isset($1) ? $1 : $2', $value);
        if (!$this->pipe_enable) {
            return $this->fix_namespace_class($result);
        }
        return $this->pipe_dream($this->fix_namespace_class($result));
    }

    /**
     * It converts a string separated by pipes | into a filtered expression.<br>
     * If the method exists (as directive), then it is used<br>
     * If the method exists (in this class) then it is used<br>
     * Otherwise, it uses a global function.<br>
     * If you want to escape the "|", then you could use "/|"<br>
     * **Note:** It only works if $this->pipe_enable=true and by default it is false<br>
     * **Example:**<br>
     * ```
     * $this->pipe_dream('$name | strtolower | substr:0,4'); // strtolower(substr($name ,0,4)
     * $this->pipe_dream('$name| get_mode') // $this->get_mode($name)
     * ```
     *
     * @param string $result
     * @return string
     * Compiler::$pipe_enable
     */
    protected function pipe_dream($result): string
    {
        $array = preg_split('~\\\\.(*SKIP)(*FAIL)|\|~s', $result);
        $c = count($array) - 1; // base zero.
        if ($c === 0) {
            return $result;
        }
        $prev = '';
        for ($i = 1; $i <= $c; $i++) {
            $r = @explode(':', $array[$i], 2);
            $fnName = trim($r[0]);
            $fnNameF = $fnName[0]; // first character
            if ($fnNameF === '"' || $fnNameF === '\'' || $fnNameF === '$' || is_numeric($fnNameF)) {
                $fnName = '!isset(' . $array[0] . ') ? ' . $fnName . ' : ';
            } elseif (isset($this->custom_directives[$fnName])) {
                $fnName = '$this->custom_directives[\'' . $fnName . '\']';
            } elseif (method_exists($this, $fnName)) {
                $fnName = '$this->' . $fnName;
            }
            $hasArgument = count($r) === 2;
            if ($i === 1) {
                $prev = $fnName . '(' . $array[0];
                if ($hasArgument) {
                    $prev .= ',' . $r[1];
                }
                $prev .= ')';
            } else {
                $prev = $fnName . '(' . $prev;
                if ($hasArgument) {
                    $prev .= ',' . $r[1] . ')';
                } else {
                    $prev .= ')';
                }
            }
        }
        return $prev;
    }

    /**
     * Compile the "regular" echo statements. {{ }}
     *
     * @param string $value
     * @return string
     */
    protected function compile_regularechos($value): string
    {
        $pattern = \sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->content_tags[0], $this->content_tags[1]);
        $callback = function($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];
            $wrapped = \sprintf($this->echo_format, $this->compile_echodefaults($matches[2]));
            return $matches[1] ? \substr($matches[0], 1) : $this->php_tag_echo . $wrapped . '; ?>' . $whitespace;
        };
        return \preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the escaped echo statements. {!! !!}
     *
     * @param string $value
     * @return string
     */
    protected function compile_escapedechos($value): string
    {
        $pattern = \sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escaped_tags[0], $this->escaped_tags[1]);
        $callback = function($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];
            return $matches[1] ? $matches[0] : $this->php_tag
                . \sprintf($this->echo_format, $this->compile_echodefaults($matches[2])) . '; ?>'
                . $whitespace;
            //return $matches[1] ? $matches[0] : $this->php_tag
            // . 'echo static::e(' . $this->compile_echodefaults($matches[2]) . '); ? >' . $whitespace;
        };
        return \preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the "@each" tag into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_each($expression): string
    {
        return $this->php_tag_echo . "\$this->render_each$expression; ?>";
    }

    protected function compile_set($expression): string
    {
        //$segments = \explode('=', \preg_replace("/[()\\\']/", '', $expression));
        $segments = \explode('=', $this->strip_parentheses($expression));
        $value = (\count($segments) >= 2) ? '=@' . implode('=', array_slice($segments, 1)) : '++';
        return $this->php_tag . \trim($segments[0]) . $value . ';?>';
    }

    /**
     * Compile the yield statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_yield($expression): string
    {
        return $this->php_tag_echo . "\$this->yield_content$expression; ?>";
    }

    /**
     * Compile the show statements into valid PHP.
     *
     * @return string
     */
    protected function compile_show(): string
    {
        return $this->php_tag_echo . '$this->yield_section(); ?>';
    }

    /**
     * Compile the section statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_section($expression): string
    {
        return $this->php_tag . "\$this->start_section$expression; ?>";
    }

    /**
     * Compile the append statements into valid PHP.
     *
     * @return string
     */
    protected function compile_append(): string
    {
        return $this->php_tag . '$this->append_section(); ?>';
    }

    /**
     * Compile the auth statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_auth($expression = ''): string
    {
        $role = $this->strip_parentheses($expression);
        if ($role == '') {
            return $this->php_tag . 'if(isset($this->current_user)): ?>';
        }
        return $this->php_tag . "if(isset(\$this->current_user) && \$this->current_role==$role): ?>";
    }

    /**
     * Compile the elseauth statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_elseauth($expression = ''): string
    {
        $role = $this->strip_parentheses($expression);
        if ($role == '') {
            return $this->php_tag . 'else: ?>';
        }
        return $this->php_tag . "elseif(isset(\$this->current_user) && \$this->current_role==$role): ?>";
    }

    /**
     * Compile the end-auth statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endauth(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    protected function compile_can($expression): string
    {
        $v = $this->strip_parentheses($expression);
        return $this->php_tag . 'if (call_user_func($this->auth_call_back,' . $v . ')): ?>';
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_elsecan($expression = ''): string
    {
        $v = $this->strip_parentheses($expression);
        if ($v) {
            return $this->php_tag . 'elseif (call_user_func($this->auth_call_back,' . $v . ')): ?>';
        }
        return $this->php_tag . 'else: ?>';
    }
    //</editor-fold>
    //<editor-fold desc="file members">
    protected function compile_cannot($expression): string
    {
        $v = $this->strip_parentheses($expression);
        return $this->php_tag . 'if (!call_user_func($this->auth_call_back,' . $v . ')): ?>';
    }

    /**
     * Compile the elsecannot statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_elsecannot($expression = ''): string
    {
        $v = $this->strip_parentheses($expression);
        if ($v) {
            return $this->php_tag . 'elseif (!call_user_func($this->auth_call_back,' . $v . ')): ?>';
        }
        return $this->php_tag . 'else: ?>';
    }

    /**
     * Compile the canany statements into valid PHP.
     * canany(['edit','write'])
     *
     * @param $expression
     * @return string
     */
    protected function compile_canany($expression): string
    {
        $role = $this->strip_parentheses($expression);
        return $this->php_tag . 'if (call_user_func($this->auth_any_call_back,' . $role . ')): ?>';
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @param $expression
     * @return string
     */
    protected function compile_elsecanany($expression): string
    {
        $role = $this->strip_parentheses($expression);
        if ($role == '') {
            return $this->php_tag . 'else: ?>';
        }
        return $this->php_tag . 'elseif (call_user_func($this->auth_any_call_back,' . $role . ')): ?>';
    }

    /**
     * Compile the guest statements into valid PHP.
     *
     * @param null $expression
     * @return string
     */
    protected function compile_guest($expression = null): string
    {
        if ($expression === null) {
            return $this->php_tag . 'if(!isset($this->current_user)): ?>';
        }
        $role = $this->strip_parentheses($expression);
        if ($role == '') {
            return $this->php_tag . 'if(!isset($this->current_user)): ?>';
        }
        return $this->php_tag . "if(!isset(\$this->current_user) || \$this->current_role!=$role): ?>";
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @param $expression
     * @return string
     */
    protected function compile_elseguest($expression): string
    {
        $role = $this->strip_parentheses($expression);
        if ($role == '') {
            return $this->php_tag . 'else: ?>';
        }
        return $this->php_tag . "elseif(!isset(\$this->current_user) || \$this->current_role!=$role): ?>";
    }

    /**
     * /**
     * Compile the end-auth statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endguest(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    /**
     * Compile the end-section statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endsection(): string
    {
        return $this->php_tag . '$this->stop_section(); ?>';
    }

    /**
     * Compile the stop statements into valid PHP.
     *
     * @return string
     */
    protected function compile_stop(): string
    {
        return $this->php_tag . '$this->stop_section(); ?>';
    }

    /**
     * Compile the overwrite statements into valid PHP.
     *
     * @return string
     */
    protected function compile_overwrite(): string
    {
        return $this->php_tag . '$this->stop_section(true); ?>';
    }

    /**
     * Compile the unless statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_unless($expression): string
    {
        return $this->php_tag . "if ( ! $expression): ?>";
    }

    /**
     * Compile the User statements into valid PHP.
     *
     * @return string
     */
    protected function compile_user(): string
    {
        return $this->php_tag_echo . "'" . $this->current_user . "'; ?>";
    }

    /**
     * Compile the endunless statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endunless(): string
    {
        return $this->php_tag . 'endif; ?>';
    }
    //</editor-fold>
    //<editor-fold desc="Array Functions">
    /**
     * @error('key')
     *
     * @param $expression
     * @return string
     */
    protected function compile_error($expression): string
    {
        $key = $this->strip_parentheses($expression);
        return $this->php_tag . '$message = call_user_func($this->error_call_back,' . $key . '); if ($message): ?>';
    }

    /**
     * Compile the end-error statements into valid PHP.
     *
     * @return string
     */
    protected function compile_enderror(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @return string
     */
    protected function compile_else(): string
    {
        return $this->php_tag . 'else: ?>';
    }

    /**
     * Compile the for statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_for($expression): string
    {
        return $this->php_tag . "for$expression: ?>";
    }
    //</editor-fold>
    //<editor-fold desc="string functions">
    /**
     * Compile the foreach statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_foreach($expression): string
    {
        //\preg_match('/\( *(.*) * as *([^\)]*)/', $expression, $matches);
        if ($expression === null) {
            return '@foreach';
        }
        \preg_match('/\( *(.*) * as *([^)]*)/', $expression, $matches);
        $iteratee = \trim($matches[1]);
        $iteration = \trim($matches[2]);
        $initLoop = "\$__currentLoopData = $iteratee; \$this->add_loop(\$__currentLoopData);\$this->get_first_loop();\n";
        $iterateLoop = '$loop = $this->increment_loop_indices(); ';
        return $this->php_tag . "$initLoop foreach(\$__currentLoopData as $iteration): $iterateLoop ?>";
    }

    /**
     * Compile a split of a foreach cycle. Used for example when we want to separate limites each "n" elements.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_splitforeach($expression): string
    {
        return $this->php_tag_echo . '$this::split_foreach' . $expression . '; ?>';
    }

    /**
     * Compile the break statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_break($expression): string
    {
        return $expression ? $this->php_tag . "if$expression break; ?>" : $this->php_tag . 'break; ?>';
    }

    /**
     * Compile the continue statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_continue($expression): string
    {
        return $expression ? $this->php_tag . "if$expression continue; ?>" : $this->php_tag . 'continue; ?>';
    }

    /**
     * Compile the forelse statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_forelse($expression): string
    {
        $empty = '$__empty_' . ++$this->forelse_counter;
        return $this->php_tag . "$empty = true; foreach$expression: $empty = false; ?>";
    }

    /**
     * Compile the if statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_if($expression): string
    {
        return $this->php_tag . "if$expression: ?>";
    }
    //</editor-fold>
    //<editor-fold desc="loop functions">
    /**
     * Compile the else-if statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_elseif($expression): string
    {
        return $this->php_tag . "elseif$expression: ?>";
    }

    /**
     * Compile the forelse statements into valid PHP.
     *
     * @param string $expression empty if it's inside a for loop.
     * @return string
     */
    protected function compile_empty($expression = ''): string
    {
        if ($expression == '') {
            $empty = '$__empty_' . $this->forelse_counter--;
            return $this->php_tag . "endforeach; if ($empty): ?>";
        }
        return $this->php_tag . "if (empty$expression): ?>";
    }

    /**
     * Compile the has section statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_hassection($expression): string
    {
        return $this->php_tag . "if (! empty(trim(\$this->yield_content$expression))): ?>";
    }

    /**
     * Compile the end-while statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endwhile(): string
    {
        return $this->php_tag . 'endwhile; ?>';
    }

    /**
     * Compile the end-for statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endfor(): string
    {
        return $this->php_tag . 'endfor; ?>';
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endforeach(): string
    {
        return $this->php_tag . 'endforeach; $this->pop_loop(); $loop = $this->get_first_loop(); ?>';
    }

    /**
     * Compile the end-can statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endcan(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    /**
     * Compile the end-can statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endcanany(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    /**
     * Compile the end-cannot statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endcannot(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    /**
     * Compile the end-if statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endif(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    /**
     * Compile the end-for-else statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endforelse(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    /**
     * Compile the raw PHP statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_php($expression): string
    {
        return $expression ? $this->php_tag . "$expression; ?>" : $this->php_tag;
    }

    //<editor-fold desc="setter and getters">

    /**
     * Compile end-php statement into valid PHP.
     *
     * @return string
     */
    protected function compile_endphp(): string
    {
        return ' ?>';
    }

    /**
     * Compile the unset statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_unset($expression): string
    {
        return $this->php_tag . "unset$expression; ?>";
    }

    /**
     * Compile the extends statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_extends($expression): string
    {
        $expression = $this->strip_parentheses($expression);
        // $_shouldextend avoids to runchild if it's not evaluated.
        // For example @if(something) @extends('aaa.bb') @endif()
        // If something is false then it's not rendered at the end (footer) of the script.
        $this->uid_counter++;
        $data = $this->php_tag . 'if (isset($_shouldextend[' . $this->uid_counter . '])) { echo $this->run_child(' . $expression . '); } ?>';
        $this->footer[] = $data;
        return $this->php_tag . '$_shouldextend[' . $this->uid_counter . ']=1; ?>';
    }

    /**
     * Execute the @parent command. This operation works in tandem with extend_section
     *
     * @return string
     * @see extend_section
     */
    protected function compile_parent(): string
    {
        return $this->PARENTKEY;
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_include($expression): string
    {
        $expression = $this->strip_parentheses($expression);
        return $this->php_tag_echo . '$this->run_child(' . $expression . '); ?>';
    }

    /**
     * It loads a compiled template and paste inside the code.<br>
     * It uses more disk space, but it decreases the number of includes<br>
     *
     * @param $expression
     * @return string
     * @throws Exception
     */
    protected function compile_includefast($expression): string
    {
        $expression = $this->strip_parentheses($expression);
        $ex = $this->strip_parentheses($expression);
        $exp = \explode(',', $ex);
        $file = $this->strip_quotes($exp[0] ?? null);
        $fileC = $this->get_compiled_file($file);
        if (!@\is_file($fileC)) {
            // if the file doesn't exist then it's created
            $this->compile($file, true);
        }
        return $this->get_file($fileC);
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_includeif($expression): string
    {
        return $this->php_tag . 'if ($this->template_exist' . $expression . ') echo $this->run_child' . $expression . '; ?>';
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_includewhen($expression): string
    {
        $expression = $this->strip_parentheses($expression);
        return $this->php_tag_echo . '$this->include_when(' . $expression . '); ?>';
    }

    /**
     * Compile the includefirst statement
     *
     * @param string $expression
     * @return string
     */
    protected function compile_includefirst($expression): string
    {
        $expression = $this->strip_parentheses($expression);
        return $this->php_tag_echo . '$this->include_first(' . $expression . '); ?>';
    }

    /**
     * Compile the {@}compilestamp statement.
     *
     * @param string $expression
     *
     * @return false|string
     */
    protected function compile_compilestamp($expression)
    {
        $expression = $this->strip_quotes($this->strip_parentheses($expression));
        $expression = ($expression === '') ? 'Y-m-d H:i:s' : $expression;
        return date($expression);
    }

    /**
     * compile the {@}viewname statement<br>
     * {@}viewname('compiled') returns the full compiled path
     * {@}viewname('template') returns the full template path
     * {@}viewname('') returns the view name.
     *
     * @param mixed $expression
     *
     * @return string
     */
    protected function compile_viewname($expression): string
    {
        $expression = $this->strip_quotes($this->strip_parentheses($expression));
        switch ($expression) {
            case 'compiled':
                return $this->get_compiled_file($this->file_name);
            case 'template':
                return $this->get_template_file($this->file_name);
            default:
                return $this->file_name;
        }
    }

    /**
     * Compile the stack statements into the content.
     *
     * @param string $expression
     * @return string
     * @see Compiler::yield_push_content
     */
    protected function compile_stack($expression): string
    {
        return $this->php_tag_echo . " \$this->compile_stack_final$expression; ?>";
    }

    public function compile_stack_final($a = null, $b = null): string
    {
        return $this->escape_stack0 . $a . ',' . $b . $this->escape_stack1;
    }

    /**
     * Compile the endpush statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endpush(): string
    {
        return $this->php_tag . '$this->stop_push(); ?>';
    }

    /**
     * Compile the endpushonce statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endpushonce(): string
    {
        return $this->php_tag . '$this->stop_push(); endif; ?>';
    }

    /**
     * Compile the endpush statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endprepend(): string
    {
        return $this->php_tag . '$this->stop_prepend(); ?>';
    }

    /**
     * Compile the component statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_component($expression): string
    {
        return $this->php_tag . " \$this->start_component$expression; ?>";
    }

    /**
     * Compile the end-component statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endcomponent(): string
    {
        return $this->php_tag_echo . '$this->render_component(); ?>';
    }

    /**
     * Compile the slot statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_slot($expression): string
    {
        return $this->php_tag . " \$this->slot$expression; ?>";
    }

    /**
     * Compile the end-slot statements into valid PHP.
     *
     * @return string
     */
    protected function compile_endslot(): string
    {
        return $this->php_tag . ' $this->end_slot(); ?>';
    }

    protected function compile_asset($expression): string
    {
        return $this->php_tag_echo . "(isset(\$this->asset_dict[$expression]))?\$this->asset_dict[$expression]:\$this->base_url.'/'.$expression; ?>";
    }

    protected function compile_assetcdn($expression): string
    {
        return $this->php_tag_echo . "(isset(\$this->asset_dict_cdn[$expression]))?\$this->asset_dict_cdn[$expression]:\$this->cdn_url.'/'.$expression; ?>";
    }

    protected function compile_json($expression): string
    {
        $parts = \explode(',', $this->strip_parentheses($expression));
        $options = isset($parts[1]) ? \trim($parts[1]) : JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $depth = isset($parts[2]) ? \trim($parts[2]) : 512;
        return $this->php_tag_echo . "json_encode($parts[0], $options, $depth); ?>";
    }
    //</editor-fold>
    //<editor-fold desc="attributes">
    /**
     * Compile the checked statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_checked($expression): string
    {
        return $this->php_tag . "if$expression echo 'checked'; ?>";
    }

    protected function compile_style($expression): string
    {
        return $this->php_tag . "echo 'class=\"'.\$this->runtime_style($expression).'\"' ?>";
    }

    protected function compile_class($expression): string
    {
        return $this->php_tag . "echo 'class=\"'.\$this->runtime_style($expression).'\"'; ?>";
    }

    protected function runtime_style($expression = null, $separator = ' '): string
    {
        if ($expression === null) {
            return '';
        }
        if (!is_array($expression)) {
            $expression = [$expression];
        }
        $result = '';
        foreach ($expression as $k => $v) {
            if (is_numeric($k)) {
                $result .= $v . $separator;
            } elseif ($v) {
                $result .= $k . $separator;
            }
        }
        return trim($result);
    }

    /**
     * Compile the selected statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_selected($expression): string
    {
        return $this->php_tag . "if$expression echo 'selected'; ?>";
    }

    /**
     * Compile the disabled statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_disabled($expression): string
    {
        return $this->php_tag . "if$expression echo 'disabled'; ?>";
    }

    /**
     * Compile the readonly statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_readonly($expression): string
    {
        return $this->php_tag . "if$expression echo 'readonly'; ?>";
    }

    /**
     * Compile the required statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compile_required($expression): string
    {
        return $this->php_tag . "if$expression echo 'required'; ?>";
    }
    //</editor-fold>
    // <editor-fold desc='language'>
    protected function compile_isset($expression): string
    {
        return $this->php_tag . "if(isset$expression): ?>";
    }

    protected function compile_endisset(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    protected function compile_endempty(): string
    {
        return $this->php_tag . 'endif; ?>';
    }

    //<editor-fold desc="compile">

    /**
     * Resolve a given class using the inject_resolver callable.
     *
     * @param string      $className
     * @param string|null $variableName
     * @return mixed
     */
    protected function inject_class($className, $variableName = null)
    {
        if (isset($this->inject_resolver)) {
            return call_user_func($this->inject_resolver, $className, $variableName);
        }
        $fullClassName = $className . "\\" . $variableName;
        return new $fullClassName();
    }

    /**
     * Used for @_e directive.
     *
     * @param $expression
     *
     * @return string
     */
    protected function compile_e($expression): string
    {
        return $this->php_tag_echo . "\$this->_e$expression; ?>";
    }

    /**
     * Used for @_ef directive.
     *
     * @param $expression
     *
     * @return string
     */
    protected function compile_ef($expression): string
    {
        return $this->php_tag_echo . "\$this->_ef$expression; ?>";
    }

    //</editor-fold>

    /**
     * Used for @_n directive.
     *
     * @param $expression
     *
     * @return string
     */
    protected function compile_n($expression): string
    {
        return $this->php_tag_echo . "\$this->_n$expression; ?>";
    }
    // </editor-fold>
    //<editor-fold desc="cli">
    public static function is_cli(): bool
    {
        return !http_response_code();
    }

    /**
     * @param           $key
     * @param string    $default  is the defalut value is the parameter is set
     *                            without value.
     * @param bool      $set      it is the value returned when the argument is set but there is no value assigned
     * @return string
     */
    public static function get_parameter_cli($key, $default = '', $set = true)
    {
        global $argv;
        $p = array_search('-' . $key, $argv, true);
        if ($p === false) {
            return $default;
        }
        if (isset($argv[$p + 1])) {
            return self::remove_trail_slash($argv[$p + 1]);
        }
        return $set;
    }

    protected static function remove_trail_slash($txt): string
    {
        return rtrim($txt, '/\\');
    }

    /**
     * @param string $str
     * @param string $type =['i','e','s','w'][$i]
     * @return string
     */
    public static function color_log($str, $type = 'i'): string
    {
        switch ($type) {
            case 'e': //error
                return "\033[31m$str\033[0m";
            case 's': //success
                return "\033[32m$str\033[0m";
            case 'w': //warning
                return "\033[33m$str\033[0m";
            case 'i': //info
                return "\033[36m$str\033[0m";
            case 'b':
                return "\e[01m$str\e[22m";
            default:
                return $str;
        }
    }

    public function check_health_path(): bool
    {
        echo self::color_log("Checking Health\n");
        $status = true;
        if (is_dir($this->compile_dpath)) {
            echo "Compile-path [$this->compile_dpath] is a folder " . self::color_log("OK") . "\n";
        } else {
            $status = false;
            echo "Compile-path [$this->compile_dpath] is not a folder " . self::color_log("ERROR", 'e') . "\n";
        }
        foreach ($this->template_path as $t) {
            if (is_dir($t)) {
                echo "Template-path (view) [$t] is a folder " . self::color_log("OK") . "\n";
            } else {
                $status = false;
                echo "Template-path (view) [$t] is not a folder " . self::color_log("ERROR", 'e') . "\n";
            }
        }
        $error = self::color_log('OK');
        try {
            /** @noinspection RandomApiMigrationInspection */
            $rnd = $this->compile_dpath . '/dummy' . rand(10000, 900009);
            $f = @file_put_contents($rnd, 'dummy');
            if ($f === false) {
                $status = false;
                $error = self::color_log("Unable to create file [" . $this->compile_dpath . '/dummy]', 'e');
            }
            @unlink($rnd);
        } catch (\Throwable $ex) {
            $status = false;
            $error = self::color_log($ex->getMessage(), 'e');
        }
        echo "Testing write in the compile folder [$rnd] $error\n";
        $files = @glob($this->template_path[0] . '/*');
        echo "Testing reading in the view folder [" . $this->template_path[0] . "].\n";
        echo "View(s) found :" . count($files) . "\n";
        return $status;
    }

    public function create_folders(): void
    {
        echo self::color_log("Creating Folder\n");
        echo "Creating compile folder[" . self::color_log($this->compile_dpath, 'b') . "] ";
        if (!\is_dir($this->compile_dpath)) {
            $ok = @\mkdir($this->compile_dpath, 0770, true);
            if ($ok === false) {
                echo self::color_log("Error: Unable to create folder, check the permissions\n", 'e');
            } else {
                echo self::color_log("OK\n");
            }
        } else {
            echo self::color_log("Note: folder already exist.\n", 'w');
        }
        foreach ($this->template_path as $t) {
            echo "Creating template folder [" . self::color_log($t, 'b') . "] ";
            if (!\is_dir($t)) {
                $ok = @\mkdir($t, 0770, true);
                if ($ok === false) {
                    echo self::color_log("Error: Unable to create folder, check the permissions\n", 'e');
                } else {
                    echo self::color_log("OK\n");
                }
            } else {
                echo self::color_log("Note: folder already exist.\n", 'w');
            }
        }
    }

    public function clearcompile(): int
    {
        echo self::color_log("Clearing Compile Folder\n");
        $files = glob($this->compile_dpath . '/*'); // get all file names
        $count = 0;
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                $count++;
                echo "deleting [$file] ";
                $r = @unlink($file); // delete file
                if ($r) {
                    echo self::color_log("OK\n");
                } else {
                    echo self::color_log("ERROR\n", 'e');
                }
            }
        }
        echo "Files deleted $count\n";
        return $count;
    }

    public function cli_engine(): void
    {
        $clearcompile = self::get_parameter_cli('clearcompile');
        $createfolder = self::get_parameter_cli('createfolder');
        $check = self::get_parameter_cli('check');
        echo '  ____  _           _       ____             ' . "\n";
        echo ' |  _ \| |         | |     / __ \            ' . "\n";
        echo ' | |_) | | __ _  __| | ___| |  | |_ __   ___ ' . "\n";
        echo ' |  _ <| |/ _` |/ _` |/ _ \ |  | | \'_ \ / _ \\' . "\n";
        echo ' | |_) | | (_| | (_| |  __/ |__| | | | |  __/' . "\n";
        echo ' |____/|_|\__,_|\__,_|\___|\____/|_| |_|\___|' . " V." . self::VERSION . "\n\n";
        echo "\n";
        $done = false;
        if ($check) {
            $done = true;
            $this->check_health_path();
        }
        if ($clearcompile) {
            $done = true;
            $this->clearcompile();
        }
        if ($createfolder) {
            $done = true;
            $this->create_folders();
        }
        if (!$done) {
            echo " Syntax:\n";
            echo " " . self::color_log("-templatepath", "b") . " <templatepath> (optional) the template-path (view path).\n";
            echo "    Default value: 'views'\n";
            echo "    Example: 'php /vendor/bin/bladeonecli /folder/views' (absolute)\n";
            echo "    Example: 'php /vendor/bin/bladeonecli folder/view1' (relative)\n";
            echo " " . self::color_log("-compilepath", "b") . " <compilepath>  (optional) the compile-path.\n";
            echo "    Default value: 'compiles'\n";
            echo "    Example: 'php /vendor/bin/bladeonecli /folder/compiles' (absolute)\n";
            echo "    Example: 'php /vendor/bin/bladeonecli compiles' (relative)\n";
            echo " " . self::color_log("-createfolder", "b") . " it creates the folders if they don't exist.\n";
            echo "    Example: php ./vendor/bin/bladeonecli -createfolder\n";
            echo " " . self::color_log("-clearcompile", "b") . " It deletes the content of the compile path\n";
            echo " " . self::color_log("-check", "b") . " It checks the folders and permissions\n";
        }
    }

    public static function is_absolute_path($path): bool
    {
        if (!$path) {
            return true;
        }
        if (DIRECTORY_SEPARATOR === '/') {
            // linux and macos
            return $path[0] === '/';
        }
        return $path[1] === ':';
    }
    //</editor-fold>
}

if (!function_exists("array_key_last")) {
    function array_key_last($array)
    {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }
        return array_keys($array)[count($array) - 1];
    }
}
