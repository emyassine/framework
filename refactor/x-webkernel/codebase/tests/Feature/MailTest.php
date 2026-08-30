<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Communication\Mail;
use Webkernel\Config\Config;

final class MailTest extends TestCase
{
    protected function setUp(): void
    {
        Config::boot();
        Mail::flush();
    }

    /**
     * @return void
     */
    public function test_array_driver_records_envelope(): void
    {
        Mail::make()
            ->driver('array')
            ->to('ops@example.com', 'Ops')
            ->cc('lead@example.com')
            ->from('noreply@example.com', 'Webkernel')
            ->subject('Hello')
            ->html('<p>Hi</p>')
            ->text('Hi')
            ->header('X-Job', 'welcome')
            ->send();

        $sent = Mail::sent();
        $this->assertCount(1, $sent);
        $this->assertSame('Hello', $sent[0]['subject']);
        $this->assertSame('ops@example.com', $sent[0]['to'][0]['address']);
        $this->assertSame('lead@example.com', $sent[0]['cc'][0]['address']);
        $this->assertSame('<p>Hi</p>', $sent[0]['html']);
        $this->assertSame('welcome', $sent[0]['headers']['X-Job']);
    }

    /**
     * @return void
     */
    public function test_send_without_recipient_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mail has no recipient.');
        Mail::make()->driver('array')->subject('X')->send();
    }
}
