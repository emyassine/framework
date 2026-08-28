<?php

declare(strict_types=1);

namespace Webkernel\Imagery\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Webkernel\Imagery\IconSetManager;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class GenerateIconEnumsCommand extends Command
{
    protected $signature = 'imagery:generate-enums
                            {--all : Generate enums for all installed icon sets}
                            {--path= : Custom path for generated enums (default: app/Enums/Icons)}
                            {--with-facade : Also generate the Icon facade}
                            {--no-facade : Skip the facade generation prompt}';

    protected $description = 'Generate PHP Enums for installed icon sets';

    protected IconSetManager $icon_set_manager;

    public function __construct()
    {
        parent::__construct();
        $this->icon_set_manager = new IconSetManager;
    }

    public function handle(): int
    {
        info('Webkernel Imagery - Generate Icon Enums');
        $this->newLine();

        $sets = $this->icon_set_manager->get_set_names();

        if (empty($sets)) {
            warning('No icon sets found. Please install at least one icon package first.');
            warning('Run: php artisan imagery:install-icons');

            return self::FAILURE;
        }

        if ($this->option('all')) {
            $selectedSets = $sets;
        } else {
            $options = [];
            foreach ($sets as $set) {
                $count = count($this->icon_set_manager->get_icons_for_set($set));
                $options[$set] = "{$this->format_set_name($set)} ({$count} icons)";
            }

            $selectedSets = multiselect(
                label: 'Select icon sets to generate enums for:',
                options: $options,
                default: $sets,
                required: true,
            );
        }

        // Get the package path for generated enums
        $packagePath = dirname(__DIR__).'/Enums';
        $path = $this->option('path') ?? $packagePath;

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $generated = [];

        foreach ($selectedSets as $set) {
            $enumName = $this->generate_enum_name($set);

            spin(
                message: "Generating {$enumName}...",
                callback: function () use ($set, $path, $enumName, &$generated) {
                    $this->generate_enum($set, $path, $enumName);
                    $generated[] = $enumName;
                }
            );
        }

        $this->newLine();
        info('Generated '.count($generated).' enum(s):');

        foreach ($generated as $enum) {
            $this->line("   • Webkernel\\Imagery\\Enums\\{$enum}");
        }

        $this->newLine();
        info('Usage examples:');
        $this->newLine();

        $firstEnum = $generated[0] ?? 'Heroicons';
        $this->line("   use Webkernel\\Imagery\\Enums\\{$firstEnum};");
        $this->newLine();
        $this->line('   // In navigation icon:');
        $this->line("   protected static string|BackedEnum|null \$navigationIcon = {$firstEnum}::Star;");
        $this->newLine();
        $this->line('   // In actions:');
        $this->line("   Action::make('star')->icon({$firstEnum}::Star)");
        $this->newLine();
        $this->line('   // Get icon name as string:');
        $this->line("   {$firstEnum}::Star->value // Returns full icon name");
        $this->newLine();
        $this->newLine();
        info('Or use the Icon helper class (no generation needed):');
        $this->newLine();
        $this->line('   use Webkernel\\Imagery\\Enums\\Icon;');
        $this->newLine();
        $this->line("   Icon::heroicon('users', 'outlined')");
        $this->line("   Icon::material('account-circle')");
        $this->line("   Icon::phosphor('whatsapp-logo', 'duotone')");

        // Handle facade generation
        $shouldGenerateFacade = $this->option('with-facade')
            || (! $this->option('no-facade') && confirm('Would you like to generate IconEnums facade for easier access?', default: true));

        if ($shouldGenerateFacade) {
            $this->generate_facade($path, $generated);
            $this->newLine();
            info('Generated IconEnums facade!');
            $this->newLine();
            $this->line('   use Webkernel\\Imagery\\Enums\\IconEnums;');
            $this->newLine();
            $this->line("   IconEnums::heroicons('star')     // Returns 'heroicon-o-star'");
            $this->line("   IconEnums::phosphorIcons('heart') // Returns 'phosphor-heart'");
        }

        return self::SUCCESS;
    }

    protected function generate_enum(string $set, string $path, string $enumName): void
    {
        $icons = $this->icon_set_manager->get_icons_for_set($set);
        $cases = [];

        foreach ($icons as $icon) {
            // Remove set prefix to get just the icon name
            $iconName = $this->extract_icon_name($icon, $set);
            $caseName = $this->generate_case_name($iconName);

            // Skip if case name is invalid (starts with number, etc.)
            if (! $this->is_valid_case_name($caseName)) {
                continue;
            }

            $cases[$caseName] = $icon;
        }

        // Sort cases alphabetically
        ksort($cases);

        $enumContent = $this->build_enum_content($enumName, $cases, $set);

        file_put_contents("{$path}/{$enumName}.php", $enumContent);
    }

    protected function generate_enum_name(string $set): string
    {
        // Convert set name to PascalCase enum name
        // heroicons -> Heroicons
        // fontawesome-solid -> FontawesomeSolid
        // phosphor-icons -> PhosphorIcons
        // google-material-design-icons -> GoogleMaterialDesignIcons

        return Str::of($set)
            ->replace(['-', '_'], ' ')
            ->title()
            ->replace(' ', '')
            ->toString();
    }

    protected function extract_icon_name(string $fullIconName, string $set): string
    {
        // Remove the set prefix
        // heroicon-o-star -> o-star
        // phosphor-star -> star
        // gmdi-star -> star

        $prefixes = [
            'heroicons' => 'heroicon-',
            'fontawesome-solid' => 'fas-',
            'fontawesome-regular' => 'far-',
            'fontawesome-brands' => 'fab-',
            'phosphor-icons' => 'phosphor-',
            'google-material-design-icons' => 'gmdi-',
            'tabler-icons' => 'tabler-',
            'lucide' => 'lucide-',
            'remix-icon' => 'ri-',
            'bootstrap-icons' => 'bi-',
        ];

        $prefix = $prefixes[$set] ?? "{$set}-";

        return Str::after($fullIconName, $prefix);
    }

    protected function generate_case_name(string $iconName): string
    {
        // Convert icon name to valid PHP enum case name
        // o-star -> OutlinedStar
        // s-star -> SolidStar
        // m-star -> MiniStar
        // star -> Star
        // arrow-up -> ArrowUp
        // user-circle -> UserCircle

        $name = $iconName;

        // Handle Heroicon prefixes - need to convert rest to PascalCase first
        $prefix = '';
        $rest = $name;

        if (Str::startsWith($name, 'o-')) {
            $prefix = 'Outlined';
            $rest = Str::after($name, 'o-');
        } elseif (Str::startsWith($name, 's-')) {
            $prefix = 'Solid';
            $rest = Str::after($name, 's-');
        } elseif (Str::startsWith($name, 'm-')) {
            $prefix = 'Mini';
            $rest = Str::after($name, 'm-');
        } elseif (Str::startsWith($name, 'c-')) {
            $prefix = 'Compact';
            $rest = Str::after($name, 'c-');
        }

        // Convert rest to PascalCase
        $pascalRest = Str::of($rest)
            ->replace(['-', '_', '.'], ' ')
            ->title()
            ->replace(' ', '')
            ->toString();

        return $prefix.$pascalRest;
    }

    protected function is_valid_case_name(string $name): bool
    {
        // Must start with a letter and contain only alphanumeric characters
        if (empty($name)) {
            return false;
        }

        // Can't start with a number
        if (is_numeric($name[0])) {
            return false;
        }

        // Must be valid PHP identifier
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) !== 1) {
            return false;
        }

        // PHP reserved words that cannot be used as enum case names
        $reservedWords = [
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'callable',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exit',
            'extends',
            'final',
            'finally',
            'fn',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'include',
            'include_once',
            'instanceof',
            'insteadof',
            'interface',
            'isset',
            'list',
            'match',
            'namespace',
            'new',
            'or',
            'print',
            'private',
            'protected',
            'public',
            'readonly',
            'require',
            'require_once',
            'return',
            'static',
            'switch',
            'throw',
            'trait',
            'try',
            'unset',
            'use',
            'var',
            'while',
            'xor',
            'yield',
            'true',
            'false',
            'null',
            'self',
            'parent',
        ];

        return ! in_array(strtolower($name), $reservedWords, true);
    }

    protected function build_enum_content(string $enumName, array $cases, string $set): string
    {
        $casesCode = '';

        foreach ($cases as $caseName => $iconValue) {
            $casesCode .= "    case {$caseName} = '{$iconValue}';\n";
        }

        return <<<PHP
<?php

namespace Webkernel\Imagery\Enums;

use Filament\Support\Contracts\ScalableIcon;
use Filament\Support\Enums\IconSize;

/**
 * Icon enum for {$set}
 *
 * Generated by Webkernel Imagery
 *
 * @see https://github.com/webkernelphp/imagery
 */
enum {$enumName}: string implements ScalableIcon
{
{$casesCode}
    /**
     * Get the icon name for the given size.
     */
    public function getIconForSize(IconSize \$size): string
    {
        return \$this->value;
    }

    /**
     * Get the icon name as a string.
     */
    public function toString(): string
    {
        return \$this->value;
    }

    /**
     * Get all available icons.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * Search for icons by name.
     *
     * @return array<self>
     */
    public static function search(string \$query): array
    {
        return array_filter(
            self::cases(),
            fn (self \$icon) => str_contains(strtolower(\$icon->name), strtolower(\$query))
        );
    }
}

PHP;
    }

    protected function generate_facade(string $path, array $enums): void
    {
        $methods = '';
        $uses = '';

        foreach ($enums as $enum) {
            $methodName = Str::camel($enum);
            $uses .= "use Webkernel\\Imagery\\Enums\\{$enum};\n";
            $methods .= <<<PHP

    /**
     * Get an icon from {$enum}.
     */
    public static function {$methodName}(string \$name): ?string
    {
        \$caseName = self::to_case_name(\$name);

        foreach ({$enum}::cases() as \$case) {
            if (\$case->name === \$caseName || str_contains(strtolower(\$case->name), strtolower(\$name))) {
                return \$case->value;
            }
        }

        return null;
    }

PHP;
        }

        $content = <<<PHP
<?php

namespace Webkernel\Imagery\Enums;

use Illuminate\Support\Str;

{$uses}
/**
 * Icon facade for easy access to all icon enums.
 *
 * Generated by Webkernel Imagery
 */
class IconEnums
{
{$methods}
    /**
     * Convert an icon name to a valid case name.
     */
    protected static function to_case_name(string \$name): string
    {
        return Str::of(\$name)
            ->replace(['-', '_', '.'], ' ')
            ->title()
            ->replace(' ', '')
            ->toString();
    }

    /**
     * Get all available icons from all sets.
     *
     * @return array<string, array<string, string>>
     */
    public static function all(): array
    {
        return [

PHP;

        foreach ($enums as $enum) {
            $content .= "            '{$enum}' => {$enum}::options(),\n";
        }

        $content .= <<<'PHP'
        ];
    }
}

PHP;

        file_put_contents("{$path}/IconEnums.php", $content);
    }

    protected function format_set_name(string $set): string
    {
        return Str::of($set)
            ->replace(['-', '_'], ' ')
            ->title()
            ->toString();
    }
}
