<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Config\Config;
use Webkernel\I18n\Catalog;
use Webkernel\I18n\I18nContext;

final class FastI18nTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        Config::boot();
        I18nContext::flush();
        Catalog::flush();
    }

    /**
     * @return void
     */
    public function test_fast_i18n_hits_the_active_locale_then_falls_back(): void
    {
        $map = ['en' => 'Hello', 'fr' => 'Bonjour'];

        $this->assertSame('Bonjour', fast_i18n($map, 'fr', 'greeting'));
        $this->assertSame('Hello', fast_i18n($map, 'de', 'greeting'));
        $this->assertSame('greeting', fast_i18n(['fr' => ''], 'de', 'greeting', 'en'));
    }

    /**
     * @return void
     */
    public function test_fast_i18n_reads_i18n_context_when_locale_omitted(): void
    {
        I18nContext::set_locale('fr');
        $this->assertSame('Bonjour', fast_i18n(['en' => 'Hello', 'fr' => 'Bonjour'], null, 'greeting'));
    }

    /**
     * @return void
     */
    public function test_fast_i18n_model_unwraps_the_envelope(): void
    {
        $column = translated_value(['en' => 'Invoice', 'fr' => 'Facture']);
        $this->assertSame('Facture', fast_i18n_model($column, 'fr'));
        $this->assertSame('Bare', fast_i18n_model('Bare', 'fr'));
    }

    /**
     * @return void
     */
    public function test_lang_reads_provider_lang_path(): void
    {
        I18nContext::set_locale('fr');
        $this->assertSame('Facture', lang('invoice.title'));
        $this->assertSame('Invoice', lang('invoice.title', [], 'en'));
    }

    /**
     * @return void
     */
    public function test_direction_and_catalog(): void
    {
        $this->assertSame('rtl', i18n_direction('ar'));
        $this->assertTrue(i18n_is_rtl('he'));
        $this->assertFalse(i18n_is_rtl('en'));
        $this->assertContains('fr', i18n_catalog_languages());
        $this->assertStringContainsString('(ar)', i18n_catalog_language_label('ar'));
    }
}
