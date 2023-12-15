<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\Attributes\Service;

#[Service]
readonly class StringProtector
{
    private const ENCODING_PREFIX = 'b64:';
    private const PREFIX_LENGTH = 4;

    public function encode(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (!mb_check_encoding($value, 'UTF-8') || str_starts_with($value, self::ENCODING_PREFIX)) {
            $value = self::ENCODING_PREFIX . base64_encode($value);
        }

        return $value;
    }

    public function decode(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (str_starts_with($value, self::ENCODING_PREFIX)) {
            $value = base64_decode(substr($value, self::PREFIX_LENGTH), true);
        }

        return $value;
    }
}
