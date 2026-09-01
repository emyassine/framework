<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Notifications\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Webkernel\Platform\Notifications\Notification;

final class NotificationTest extends TestCase
{
    /**
     * @return void
     */
    public function test_notification_flashes_to_session_and_pulls(): void
    {
        Notification::make('test-1')
            ->title('Title Test')
            ->body('Body Test')
            ->success()
            ->send();

        $items = Notification::pull();

        $this->assertCount(1, $items);
        $this->assertSame('test-1', $items[0]['id']);
        $this->assertSame('Title Test', $items[0]['title']);
        $this->assertSame('Body Test', $items[0]['body']);
        $this->assertSame('success', $items[0]['status']);
    }
}
