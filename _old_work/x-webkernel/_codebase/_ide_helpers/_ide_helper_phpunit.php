<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
//> IDE helper stubs for PHPUnit

namespace PHPUnit\Framework {
    if (false) {
        abstract class TestCase {
            public function assertStringContainsString(string $needle, string $haystack, string $message = ''): void {}
            public function assertStringNotContainsString(string $needle, string $haystack, string $message = ''): void {}
            public function assertTrue(bool $condition, string $message = ''): void {}
            public function assertFalse(bool $condition, string $message = ''): void {}
            public function assertEquals(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertSame(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertNotSame(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertNull(mixed $actual, string $message = ''): void {}
            public function assertNotNull(mixed $actual, string $message = ''): void {}
            public function assertEmpty(mixed $actual, string $message = ''): void {}
            public function assertNotEmpty(mixed $actual, string $message = ''): void {}
            /**
             * @param \Countable|array $haystack
             */
            public function assertCount(int $expectedCount, $haystack, string $message = ''): void {}
            public function assertInstanceOf(string $expectedClass, mixed $actual, string $message = ''): void {}
            public function assertNotInstanceOf(string $expectedClass, mixed $actual, string $message = ''): void {}
            /**
             * @param iterable $haystack
             */
            public function assertContains(mixed $needle, $haystack, string $message = ''): void {}
            /**
             * @param iterable $haystack
             */
            public function assertNotContains(mixed $needle, $haystack, string $message = ''): void {}
            public function assertGreaterThan(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertGreaterThanOrEqual(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertLessThan(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertLessThanOrEqual(mixed $expected, mixed $actual, string $message = ''): void {}
            public function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void {}
            public function assertDoesNotMatchRegularExpression(string $pattern, string $string, string $message = ''): void {}
            public function assertFileExists(string $filename, string $message = ''): void {}
            public function assertFileDoesNotExist(string $filename, string $message = ''): void {}
            public function assertDirectoryExists(string $directory, string $message = ''): void {}
            public function assertDirectoryDoesNotExist(string $directory, string $message = ''): void {}
        }
    }
}
