<?php declare(strict_types=1);

namespace Webkernel\Container\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Singleton {}
