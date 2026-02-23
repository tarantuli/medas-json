<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\Attributes\Service;

#[Service]
readonly class StringProtector
{
    private const string ENCODING_PREFIX = 'b64:';
    private const int PREFIX_LENGTH = 4;

    /** Ensures that all byte strings in the given value are represented as valid Unicode strings */
    public function encode(mixed $value, bool $urlSafe = false): mixed
    {
        if (is_iterable($value)) {
            $encoded = [];

            foreach ($value as $key => $subValue) {
                $encoded[$this->encode($key, $urlSafe)] = $this->encode($subValue, $urlSafe);
            }

            return $encoded;
        }

        if (!is_string($value)) {
            return $value;
        }

        if (!mb_check_encoding($value, 'UTF-8') || str_starts_with($value, self::ENCODING_PREFIX)) {
            $value = self::ENCODING_PREFIX . base64_encode($value);

            if ($urlSafe) {
                $value = str_replace(['+', '/', '='], ['-', '_', ''], $value);
            }
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
            // Remove the encoding prefix and undo any url-safe replacements
            $prepared = str_replace(['-', '_'], ['+', '/'], substr($value, self::PREFIX_LENGTH));
            $decoded = base64_decode($prepared, true);

            if ($decoded === false) {
                throw new Exceptions\InvalidBase64String($value);
            }

            $value = $decoded;
        }

        return $value;
    }
}
