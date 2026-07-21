<?php

declare(strict_types=1);

namespace Medas\Json;

readonly class Settings
{
    public function __construct(
        public bool $prettyPrint = false,
        public bool $urlSafe = false,
        public bool $normalizeObjects = true,
    )
    {
    }
}
