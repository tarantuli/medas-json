<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\Attributes\Service;

#[Service]
readonly class InvalidUtf8OffsetFinder
{
    public function find(string $string): int|null
    {
        $length = strlen($string);
        $i = 0;

        while ($i < $length) {
            $byte = ord($string[$i]);

            // Determine how many continuation bytes this leading byte expects
            $extraBytes = match (true) {
                $byte < 0x80 => 0,
                ($byte & 0xE0) === 0xC0 => 1,
                ($byte & 0xF0) === 0xE0 => 2,
                ($byte & 0xF8) === 0xF0 => 3,
                default => -1,
            };

            if ($extraBytes === -1) {
                return $i;
            }

            for ($j = 1; $j <= $extraBytes; $j++) {
                if ($i + $j >= $length || (ord($string[$i + $j]) & 0xC0) !== 0x80) {
                    return $i;
                }
            }

            $i += $extraBytes + 1;
        }

        return null;
    }
}
