<?php

declare(strict_types=1);

namespace Webkernel\Imagery\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class InstallIconsCommand extends Command
{
    protected $signature = 'imagery:install-icons
                            {--all : Install all available icon packages}
                            {--list : List all available icon packages}
                            {--no-config : Skip updating the config file}
                            {--no-enums : Skip generating icon enums}
                            {--no-facade : Skip generating IconEnums facade}';

    protected $description = 'Install icon packages for Webkernel Imagery';

    /**
     * @var array<string, array{package: string, description: string, icons: string, sets: array<string>}>
     */
    protected array $icon_packages = [
        'heroicons' => [
            'package' => 'blade-ui-kit/blade-heroicons',
            'description' => 'Heroicons by Tailwind CSS',
            'icons' => '~1,300',
            'sets' => ['heroicons'],
        ],
        'fontawesome' => [
            'package' => 'owenvoke/blade-fontawesome',
            'description' => 'Font Awesome (Solid, Regular, Brands)',
            'icons' => '~2,800',
            'sets' => ['fontawesome-solid', 'fontawesome-regular', 'fontawesome-brands'],
        ],
        'phosphor' => [
            'package' => 'codeat3/blade-phosphor-icons',
            'description' => 'Phosphor Icons',
            'icons' => '~9,000',
            'sets' => ['phosphor-icons'],
        ],
        'material' => [
            'package' => 'codeat3/blade-google-material-design-icons',
            'description' => 'Google Material Design',
            'icons' => '~10,000',
            'sets' => ['google-material-design-icons'],
        ],
        'tabler' => [
            'package' => 'secondnetwork/blade-tabler-icons',
            'description' => 'Tabler Icons',
            'icons' => '~4,400',
            'sets' => ['tabler'],
        ],
        'lucide' => [
            'package' => 'mallardduck/blade-lucide-icons',
            'description' => 'Lucide Icons',
            'icons' => '~1,400',
            'sets' => ['lucide'],
        ],
        'bootstrap' => [
            'package' => 'davidhsianturi/blade-bootstrap-icons',
            'description' => 'Bootstrap Icons',
            'icons' => '~2,000',
            'sets' => ['bootstrap-icons'],
        ],
        'remix' => [
            'package' => 'andreiio/blade-remix-icon',
            'description' => 'Remix Icons',
            'icons' => '~2,800',
            'sets' => ['remix'],
        ],
        'hugeicons' => [
            'package' => 'afatmustafa/blade-hugeicons',
            'description' => 'Hugeicons (Free)',
            'icons' => '~5,800',
            'sets' => ['hugeicons'],
        ],
    ];

    public function handle(): int
    {
        $this->display_banner();

        if ($this->option('list')) {
            return $this->list_packages();
        }

        if ($this->option('all')) {
            return $this->install_all();
        }

        return $this->interactive_install();
    }

    protected function display_banner(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║</>                                                              <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>   <fg=white;options=bold>Webkernel Imagery</> - Icon Installer                 <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                              <fg=cyan>║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════════════════╝</>');
        $this->newLine();
    }

    protected function list_packages(): int
    {
        info('Available Icon Packages');
        $this->newLine();

        $rows = [];
        foreach ($this->icon_packages as $key => $info) {
            $installed = $this->is_installed($info['package']);
            $rows[] = [
                $installed ? '✅' : '⬜',
                ucfirst($key),
                $info['icons'],
                implode(', ', $info['sets']),
                $installed ? 'Installed' : 'Not installed',
            ];
        }

        table(
            headers: ['', 'Name', 'Icons', 'Prefixes', 'Status'],
            rows: $rows
        );

        $this->newLine();
        note('Run "php artisan imagery:install-icons" to install packages interactively.');

        return self::SUCCESS;
    }

    protected function install_all(): int
    {
        $packagesToInstall = [];
        $allSets = [];

        foreach ($this->icon_packages as $info) {
            if (! $this->is_installed($info['package'])) {
                $packagesToInstall[] = $info['package'];
            }
            $allSets = array_merge($allSets, $info['sets']);
        }

        if (empty($packagesToInstall)) {
            info('All icon packages are already installed!');
            $this->update_config_if_needed($allSets);

            return self::SUCCESS;
        }

        $result = $this->install_packages($packagesToInstall);

        if ($result === self::SUCCESS) {
            $this->update_config_if_needed($allSets);
        }

        return $result;
    }

    protected function interactive_install(): int
    {
        // Build options with visual indicators
        $options = [];
        $installed = [];

        foreach ($this->icon_packages as $key => $info) {
            $is_installed = $this->is_installed($info['package']);

            if ($is_installed) {
                $installed[$key] = $info;

                continue;
            }

            $options[$key] = sprintf(
                '%-12s %s (%s)',
                ucfirst($key),
                $info['description'],
                $info['icons']
            );
        }

        // Show already installed packages
        if (! empty($installed)) {
            $this->line('<fg=green> Already installed:</>');
            foreach ($installed as $key => $info) {
                $this->line("   <fg=gray>• {$key} - {$info['icons']} icons</>");
            }
            $this->newLine();
        }

        if (empty($options)) {
            info('All icon packages are already installed!');

            return self::SUCCESS;
        }

        // Show selection prompt
        $this->line('<fg=yellow>Available to install:</>');
        $this->newLine();

        $selected = multiselect(
            label: 'Select packages to install (Space to toggle, Enter to confirm)',
            options: $options,
            default: [],
            hint: '↑↓ Navigate  •  Space Select  •  Enter Confirm',
            scroll: 10,
            required: false
        );

        if (empty($selected)) {
            warning('No packages selected. Exiting.');

            return self::SUCCESS;
        }

        // Show summary
        $this->newLine();
        $this->line('<fg=white;options=bold>Installation Summary:</>');
        $this->newLine();

        $packagesToInstall = [];
        $setsToAdd = [];

        foreach ($selected as $key) {
            $info = $this->icon_packages[$key];
            $packagesToInstall[] = $info['package'];
            $setsToAdd = array_merge($setsToAdd, $info['sets']);

            $this->line("   <fg=cyan>▸</> {$info['package']} <fg=gray>({$info['icons']} icons)</>");
        }

        $this->newLine();

        if (! confirm('Proceed with installation?', true)) {
            return self::SUCCESS;
        }

        $result = $this->install_packages($packagesToInstall);

        if ($result === self::SUCCESS && ! $this->option('no-config')) {
            $this->update_config_if_needed($setsToAdd);
        }

        return $result;
    }

    protected function is_installed(string $package): bool
    {
        $composerLock = base_path('composer.lock');

        if (! file_exists($composerLock)) {
            return false;
        }

        $content = file_get_contents($composerLock);

        return str_contains($content, '"name": "'.$package.'"');
    }

    /**
     * @param  array<string>  $packages
     */
    protected function install_packages(array $packages): int
    {
        $this->newLine();
        $failed = [];
        $successful = [];

        foreach ($packages as $package) {
            $result = spin(
                callback: function () use ($package) {
                    $output = [];
                    $code = 0;
                    exec("composer require {$package} --no-interaction 2>&1", $output, $code);

                    return $code;
                },
                message: "Installing {$package}..."
            );

            if ($result !== 0) {
                $failed[] = $package;
                $this->line("   <fg=red>✗</> Failed: {$package}");
            } else {
                $successful[] = $package;
                $this->line("   <fg=green>✓</> Installed: {$package}");
            }
        }

        $this->newLine();

        if (! empty($failed)) {
            warning('Some packages failed to install:');
            foreach ($failed as $package) {
                $this->line("   composer require {$package}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string>  $sets
     */
    protected function update_config_if_needed(array $sets): void
    {
        if ($this->option('no-config')) {
            return;
        }

        $configPath = config_path('imagery.php');

        // Check if config is published
        if (! File::exists($configPath)) {
            $this->newLine();

            if (confirm('Publish the config file to customize allowed icon sets?', true)) {
                $this->call('vendor:publish', [
                    '--tag' => 'imagery-config',
                ]);
            }
        }

        // Update the config with the installed sets
        if (File::exists($configPath)) {
            $this->update_allowed_sets($configPath, $sets);
        }

        // Generate icon enums
        $this->generate_icon_enums($sets);

        $this->display_success_message();
    }

    /**
     * @param  array<string>  $sets
     */
    protected function update_allowed_sets(string $configPath, array $sets): void
    {
        $content = File::get($configPath);
        $uniqueSets = array_unique($sets);

        // Show what will be configured
        $this->newLine();
        $this->line('<fg=white;options=bold>Icon sets to configure:</>');
        foreach ($uniqueSets as $set) {
            $this->line("   <fg=cyan>•</> {$set}");
        }
        $this->newLine();

        // Check if allowed_sets is empty (default)
        if (preg_match("/'allowed_sets'\s*=>\s*\[\s*\]/", $content)) {
            if (confirm('Update config with these icon sets?', true)) {
                $this->write_allowed_sets($configPath, $content, $uniqueSets, "/'allowed_sets'\s*=>\s*\[\s*\]/");
            }
        } elseif (preg_match("/'allowed_sets'\s*=>\s*\[/", $content)) {
            // Config has existing allowed_sets values
            note('Your config already has "allowed_sets" configured.');

            if (confirm('Replace with the selected icon sets?', true)) {
                // Match the entire allowed_sets array (multiline)
                $pattern = "/'allowed_sets'\s*=>\s*\[[^\]]*\]/s";
                $this->write_allowed_sets($configPath, $content, $uniqueSets, $pattern);
            }
        }
    }

    /**
     * @param  array<string>  $sets
     */
    protected function write_allowed_sets(string $configPath, string $content, array $sets, string $pattern): void
    {
        $setsString = "[\n        '".implode("',\n        '", $sets)."',\n    ]";
        $replacement = "'allowed_sets' => ".$setsString;

        $newContent = preg_replace($pattern, $replacement, $content);

        if ($newContent !== null && $newContent !== $content) {
            File::put($configPath, $newContent);
            info('Config updated with selected icon sets.');
        } else {
            warning('Could not update config file. Please update manually.');
        }
    }

    protected function display_success_message(): void
    {
        $this->newLine();
        $this->line('<fg=green>╔══════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=green>║</>                                                              <fg=green>║</>');
        $this->line('<fg=green>║</>    <fg=white;options=bold>Installation Complete!</>                                  <fg=green>║</>');
        $this->line('<fg=green>║</>                                                              <fg=green>║</>');
        $this->line('<fg=green>║</>   <fg=yellow>Next steps:</>                                               <fg=green>║</>');
        $this->line('<fg=green>║</>                                                              <fg=green>║</>');
        $this->line('<fg=green>║</>   1. Register the plugin in your PanelProvider               <fg=green>║</>');
        $this->line('<fg=green>║</>   2. Add views path to your Tailwind config                  <fg=green>║</>');
        $this->line('<fg=green>║</>   3. Run: npm run build                                      <fg=green>║</>');
        $this->line('<fg=green>║</>   4. Run: php artisan icons:cache                            <fg=green>║</>');
        $this->line('<fg=green>║</>                                                              <fg=green>║</>');
        $this->line('<fg=green>╚══════════════════════════════════════════════════════════════╝</>');
        $this->newLine();
    }

    /**
     * Generate icon enums for the installed sets.
     *
     * @param  array<string>  $sets
     */
    protected function generate_icon_enums(array $sets): void
    {
        if ($this->option('no-enums')) {
            return;
        }

        $this->newLine();
        info('Generating icon enums...');

        $this->call('imagery:generate-enums', [
            '--all' => true,
            '--with-facade' => ! $this->option('no-facade'),
            '--no-facade' => $this->option('no-facade'),
        ]);
    }
}
