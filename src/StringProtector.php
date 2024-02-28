<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\Attributes\Service;

#[Service]
readonly class StringProtector
{
    private const ENCODING_PREFIX = 'b64:';
    private const PREFIX_LENGTH = 4;

    /** Ensures that all byte strings in the given value are represented as valid unicode strings */
    public function encode(mixed $value): mixed
    {
        if (is_iterable($value)) {
            $encoded = [];

            foreach ($value as $key => $subValue) {
                $encoded[$this->encode($key)] = $this->encode($subValue);
            }

            return $encoded;
        }

        if (!is_string($value)) {
            return $value;
        }

        if (!mb_check_encoding($value, 'UTF-8') || str_starts_with($value, self::ENCODING_PREFIX)) {
            $value = self::ENCODING_PREFIX . base64_encode($value);
        }

        return $value;
    }

    /** Undoes the effect of encode(), restoring the original byte strings */
    public function decode(mixed $value): mixed
    {
        if (is_iterable($value)) {
            $decoded = [];

            foreach ($value as $key => $subValue) {
                $decoded[$this->decode($key)] = $this->decode($subValue);
            }

            return $decoded;
        }

        if (!is_string($value)) {
            return $value;
        }

        if (str_starts_with($value, self::ENCODING_PREFIX)) {
            $value = base64_decode(substr($value, self::PREFIX_LENGTH), true);
        }

        return $value;
    }
}
