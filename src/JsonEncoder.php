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
    public function encode(mixed $data, Settings|null $settings = null): string
    {
        $settings ??= $this->settingsFactory->create();
        $data = $this->stringProtector->encode($data, $settings->urlSafe);
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        if ($settings->prettyPrint) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, flags: $flags);
    }

    /**
     * Decodes the given JSON string into a PHP value.
     *
     * The $settings variable is provided for future compatibility and will be ignored for now.
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function decode(string $string, Settings|null $settings = null): mixed
    {
        $data = json_decode($string, flags: JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR);

        return $this->stringProtector->decode($data);
    }
}
