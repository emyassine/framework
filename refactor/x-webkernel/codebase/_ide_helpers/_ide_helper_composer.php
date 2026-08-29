<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
//> IDE helper stubs for Composer

namespace Composer {
    class Composer {
        public function getInstallationManager(): object { return new \stdClass(); }
    }
}

namespace Composer\Plugin {
    interface PluginInterface {}
}

namespace Composer\IO {
    interface IOInterface {}
}

namespace Composer\Script {
    class Event {}
    final class ScriptEvents {
        public const POST_AUTOLOAD_DUMP = 'post-autoload-dump';
    }
}

namespace Composer\EventDispatcher {
    interface EventSubscriberInterface {
        /** @return array<string,string> */
        public static function getSubscribedEvents(): array;
    }
}
