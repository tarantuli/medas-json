<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\Attributes\Service;

#[Service]
readonly class JsonEncoder
{
    public function __construct(
        private SettingsFactory $settingsFactory,
        private StringProtector $stringProtector,
    )
    {
    }

    /**
     * Encodes the given data to a JSON string. It ensures that all byte strings in the given value are represented as
     * valid Unicode strings.
     */
    public function encode(mixed $data, Settings $settings = null): string
    {
        $settings ??= $this->settingsFactory->create();
        $data = $this->stringProtector->encode($data);
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        if ($settings->prettyPrint) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, flags: $flags);
    }

    public function decode(string $string): mixed
    {
        $data = json_decode($string, flags: JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR);

        return $this->stringProtector->decode($data);
    }
}
