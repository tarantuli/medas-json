<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\{
    Attributes\DataHolder,
    Attributes\PreferredDefault,
    Attributes\Service,
    Interfaces\Serializer
};

#[Service]
readonly class StringProtector
{
    private const string ENCODING_PREFIX = 'b64:';
    private const int PREFIX_LENGTH = 4;

    public function __construct(
        #[PreferredDefault('Medas\ObjectToArraySerializer\ObjectToArraySerializer')]
        private Serializer $serializer,
    )
    {
    }

    /** Ensures that all byte strings in the given value are represented as valid Unicode strings */
    public function encode(mixed $value, bool $urlSafe = false): mixed
    {
        if (is_string($value)) {
            if (!mb_check_encoding($value, 'UTF-8') || str_starts_with($value, self::ENCODING_PREFIX)) {
                $encoded = self::ENCODING_PREFIX . base64_encode($value);

                return $urlSafe ? str_replace(['+', '/', '='], ['-', '_', ''], $encoded) : $encoded;
            }

            return $value;
        }

        if (is_object($value) && attribute(DataHolder::class, new \ReflectionClass($value))) {
            return $this->encode($this->serializer->serialize($value), $urlSafe);
        }

        if (is_object($value) && !$value instanceof \BackedEnum) {
            throw new Exceptions\ObjectFoundInValue($value);
        }

        if (!is_iterable($value)) {
            return $value;
        }

        $encoded = [];

        foreach ($value as $key => $subValue) {
            $encodedKey = is_string($key) ? $this->encode($key, $urlSafe) : $key;
            $encoded[$encodedKey] = $this->encode($subValue, $urlSafe);
        }

        return $encoded;
    }

    /** Undoes the effect of encode(), restoring the original byte strings */
    public function decode(mixed $value): mixed
    {
        if (is_string($value)) {
            if (!str_starts_with($value, self::ENCODING_PREFIX)) {
                return $value;
            }

            // Undo any url-safe replacements before decoding
            $prepared = str_replace(['-', '_'], ['+', '/'], substr($value, self::PREFIX_LENGTH));
            $decoded = base64_decode($prepared, true);

            if ($decoded === false) {
                throw new Exceptions\InvalidBase64String($value);
            }

            return $decoded;
        }

        if (!is_iterable($value)) {
            return $value;
        }

        $decoded = [];

        foreach ($value as $key => $subValue) {
            $decodedKey = is_string($key) ? $this->decode($key) : $key;
            $decoded[$decodedKey] = $this->decode($subValue);
        }

        return $decoded;
    }
}
