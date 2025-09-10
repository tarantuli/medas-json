<?php

declare(strict_types=1);

namespace Medas\JsonTest\Functional;

use Medas\Json\{JsonEncoder, Settings, StringProtector};
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    public function testBasicArray(): void
    {
        $encoder = service(JsonEncoder::class);

        $data = [
            'a' => 'b',
            1 => 2,
            '€',
            "non-unicode bytes \xF0\xA4\xAD\xA2\xF0\xA4\xAD\xA2\xF0\xA4\xAD",
        ];

        self::assertEquals($data, $encoder->decode($encoder->encode($data)));
        self::assertEquals($data, $encoder->decode($encoder->encode($data, new Settings(true))));
    }

    public function testStringProector(): void
    {
        $protector = service(StringProtector::class);

        $data = [
            'a' => 'b',
            1 => 2,
            '€',
            "non-unicode bytes \xF0\xA4\xAD\xA2\xF0\xA4\xAD\xA2\xF0\xA4\xAD",
        ];

        self::assertEquals($data, $protector->decode($protector->encode($data)));
        self::assertEquals($data, $protector->decode($protector->encode($data, true)));
    }
}
