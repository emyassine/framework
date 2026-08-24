<?php

declare(strict_types=1);

namespace Webkernel\Json\CanonicalJson;

class JsonCanonicalizationFactory
{
    public static function getInstance(): JsonCanonicalizationInterface
    {
        return new Canonicalizator();
    }
}
