<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel {
	use Webkernel\Config\Config;
	use Webkernel\Console\Input\ArgvInput;
	use Webkernel\Console\Dispatcher;
	/** CLI door. Autoload is already done by fast-boot.php */
	final class Console
	{
	   /** @param list<string> $argv @return never */
	   public static function run(array $argv): never
	   {
	       Config::boot(); exit((new Dispatcher())->handle(new ArgvInput($argv))->value);
	   }
	}
}
