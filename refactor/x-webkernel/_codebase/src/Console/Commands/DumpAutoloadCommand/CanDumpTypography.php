<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands\DumpAutoloadCommand;

use Webkernel\Platform\GeneratedFileHeader;
use Webkernel\Typography\TypographySystem;

trait CanDumpTypography
{
    use _DumpAutoloadCommand;

    //> Google Fonts returns TTF to a generic PHP UA. Chrome UA is required for woff2.
    private const FONT_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36';

    private const FONT_CSS_TIMEOUT = 20;

    private const FONT_WOFF_TIMEOUT = 120;

    private const FONT_WOFF_ATTEMPTS = 3;

    /**
     * Write WTS rules + self-hosted fonts under webapp_path("public/$typo_path").
     *
     * @return void
     */
    private function dump_typography(): void
    {
        $dir = TypographySystem::path(TypographySystem::DIR);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            $this->terminal()->warning('cannot create '.$dir);

            return;
        }
        $this->write_typography_rules();
        $this->fetch_typography_fonts($dir);
        $this->assemble_typography_fonts();
    }

    /**
     * @return void
     */
    private function write_typography_rules(): void
    {
        $source = $this->codebase_root().'/resources/css/wts.css';
        $dest = TypographySystem::path(TypographySystem::RULES_CSS);
        if (! \is_file($source)) {
            $this->terminal()->warning('missing '.$source);

            return;
        }
        $css = \file_get_contents($source);
        if (! \is_string($css)) {
            return;
        }
        \file_put_contents($dest, $this->typography_css_header('WTS rules').$css, \LOCK_EX);
    }

    /**
     * Download Google CSS + woff2 into public/fetch-fonts/{slug}/.
     *
     * //> Skip family.css already on disk. Warn on network fail; dump does not crash.
     * //> Space Grotesk is catalogued but not fetched (error watermark).
     *
     * @param $dir string
     *
     * @return void
     */
    private function fetch_typography_fonts(string $dir): void
    {
        foreach (TypographySystem::catalog() as $slug => $meta) {
            if ($meta['family'] === 'Space Grotesk') {
                continue;
            }
            $family_dir = $dir.DIRECTORY_SEPARATOR.$slug;
            $family_css = $family_dir.DIRECTORY_SEPARATOR.'family.css';
            if ($this->typography_family_complete($family_css, $family_dir)) {
                continue;
            }
            $css = $this->font_http_string(
                'https://fonts.googleapis.com/css2?family='.$meta['google'].'&display=optional',
                self::FONT_CSS_TIMEOUT,
            );
            if ($css === null || ! \str_contains($css, '@font-face')) {
                $this->terminal()->warning('font css failed: '.$meta['family']);

                continue;
            }
            $targets = TypographySystem::woff2_targets($css, $slug);
            if ($targets === []) {
                $this->terminal()->warning('no woff2 in css: '.$meta['family']);

                continue;
            }
            if (! \is_dir($family_dir) && ! \mkdir($family_dir, 0775, true) && ! \is_dir($family_dir)) {
                $this->terminal()->warning('cannot create '.$family_dir);

                continue;
            }
            $failed = 0;
            foreach ($targets as $remote => $public) {
                $dest = $family_dir.DIRECTORY_SEPARATOR.\basename($public);
                $css = \str_replace($remote, $public, $css);
                if (\is_file($dest) && (\filesize($dest) ?: 0) > 0) {
                    continue;
                }
                if (! $this->font_http_save($remote, $dest, self::FONT_WOFF_TIMEOUT)) {
                    $this->terminal()->warning('woff2 failed: '.$meta['family'].' '.\basename($public));
                    $failed++;
                }
            }
            if ($failed > 0) {
                continue;
            }
            $css = \str_replace('font-display: swap', 'font-display: optional', $css);
            \file_put_contents($family_css, $css, \LOCK_EX);
            $this->terminal()->info('fonts '.$slug.' ('.\count($targets).' woff2)');
        }
    }

    /**
     * One CSS file per script pack from local family.css. No Google @import.
     *
     * //> Pack file is omitted when no local woff2 exist; runtime then uses the CDN.
     *
     * @return void
     */
    private function assemble_typography_fonts(): void
    {
        $dir = TypographySystem::path(TypographySystem::DIR);
        foreach (TypographySystem::packs() as $pack) {
            $chunks = [];
            foreach (TypographySystem::catalog() as $slug => $meta) {
                if ($meta['family'] === 'Space Grotesk' || $meta['pack'] !== $pack) {
                    continue;
                }
                $family_css = $dir.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'family.css';
                if (! \is_file($family_css)) {
                    continue;
                }
                $chunk = \file_get_contents($family_css);
                if (! \is_string($chunk) || $chunk === '') {
                    continue;
                }
                $chunks[] = \str_replace('font-display: swap', 'font-display: optional', $chunk);
            }
            $pack_path = TypographySystem::path(TypographySystem::fonts_css($pack));
            if ($chunks === []) {
                if (\is_file($pack_path)) {
                    \unlink($pack_path);
                }

                continue;
            }
            \file_put_contents(
                $pack_path,
                $this->typography_css_header('WTS pack: '.$pack).\implode("\n", $chunks),
                \LOCK_EX,
            );
        }
    }

    /**
     * Wrap the Webkernel `//>` header in a CSS block comment.
     *
     * @param $note string
     *
     * @return string
     */
    private function typography_css_header(string $note): string
    {
        return "/*\n"
            .GeneratedFileHeader::header()."\n"
            ."//>\n"
            ."//> Generated. Do not edit.\n"
            ."//> ".$note."\n"
            ."*/\n\n";
    }

    /**
     * @param $url string
     * @param $timeout int
     *
     * @return string|null
     */
    private function font_http_string(string $url, int $timeout): ?string
    {
        $fp = $this->font_http_open($url, $timeout);
        if ($fp === null) {
            return null;
        }
        $body = \stream_get_contents($fp);
        \fclose($fp);

        return \is_string($body) && $body !== '' ? $body : null;
    }

    /**
     * True when family.css exists and every referenced woff2 is on disk.
     *
     * @param $family_css string
     * @param $family_dir string
     *
     * @return bool
     */
    private function typography_family_complete(string $family_css, string $family_dir): bool
    {
        if (! \is_file($family_css) || (\filesize($family_css) ?: 0) === 0) {
            return false;
        }
        $css = \file_get_contents($family_css);
        if (! \is_string($css) || $css === '') {
            return false;
        }
        if (\preg_match_all('#url\((/fetch-fonts/[^)]+\.woff2)\)#', $css, $matches) < 1) {
            return false;
        }
        foreach (\array_unique($matches[1]) as $public) {
            $dest = $family_dir.DIRECTORY_SEPARATOR.\basename($public);
            if (! \is_file($dest) || (\filesize($dest) ?: 0) === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Stream $url onto $dest. Rejects non-woff2 payloads. Retries on drop.
     *
     * @param $url string
     * @param $dest string
     * @param $timeout int
     *
     * @return bool
     */
    private function font_http_save(string $url, string $dest, int $timeout): bool
    {
        for ($attempt = 1; $attempt <= self::FONT_WOFF_ATTEMPTS; $attempt++) {
            if ($this->font_http_save_once($url, $dest, $timeout)) {
                return true;
            }
            if ($attempt < self::FONT_WOFF_ATTEMPTS) {
                \usleep(250_000 * $attempt);
            }
        }

        return false;
    }

    /**
     * @param $url string
     * @param $dest string
     * @param $timeout int
     *
     * @return bool
     */
    private function font_http_save_once(string $url, string $dest, int $timeout): bool
    {
        $fp = $this->font_http_open($url, $timeout);
        if ($fp === null) {
            return false;
        }
        $tmp = $dest.'.part';
        $out = \fopen($tmp, 'wb');
        if ($out === false) {
            \fclose($fp);

            return false;
        }
        $copied = @\stream_copy_to_stream($fp, $out);
        \fclose($fp);
        \fflush($out);
        \fclose($out);
        $size = \is_file($tmp) ? (\filesize($tmp) ?: 0) : 0;
        if ($copied === false || $size === 0) {
            if (\is_file($tmp)) {
                \unlink($tmp);
            }

            return false;
        }
        $magic = \file_get_contents($tmp, false, null, 0, 4);
        if ($magic !== 'wOF2') {
            \unlink($tmp);

            return false;
        }

        return \rename($tmp, $dest);
    }

    /**
     * @param $url string
     * @param $timeout int
     *
     * @return resource|null
     */
    private function font_http_open(string $url, int $timeout)
    {
        $ctx = \stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'follow_location' => 1,
                'header' => 'User-Agent: '.self::FONT_USER_AGENT."\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $fp = @\fopen($url, 'rb', false, $ctx);

        return $fp === false ? null : $fp;
    }
}
