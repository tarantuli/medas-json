<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\Attributes\Service;

#[Service]
readonly class JsonEncoder
{
    public function __construct(
        private StringProtector $stringProtector,
    )
    {
    }

    public function encode(mixed $data, bool $prettyPrint = false): string
    {
        if (is_array($data)) {
            $data = $this->encodeArray($data);
        }
        else {
            $data = $this->stringProtector->encode($data);
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        if ($prettyPrint) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, flags: $flags);
    }

    private function encodeArray(array $data): array
    {
        $encoded = [];

        foreach ($data as $key => $value) {
            $encoded[$this->stringProtector->encode($key)] = is_array($value)
                ? $this->encodeArray($value)
                : $this->stringProtector->encode($value);
        }

        return $encoded;
    }

    public function decode(string $string): mixed
    {
        $data = json_decode($string, flags: JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR);

        if (is_array($data)) {
            $data = $this->decodeArray($data);
        }
        else {
            $data = $this->stringProtector->decode($data);
        }

        return $data;
    }

    private function decodeArray(array $data): array
    {
        $decoded = [];

        foreach ($data as $key => $value) {
            $decoded[$this->stringProtector->decode($key)] = is_array($value)
                ? $this->decodeArray($value)
                : $this->stringProtector->decode($value);
        }

        return $decoded;
    }
}
