<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\Attributes\Service;

#[Service]
readonly class SettingsFactory
{
    public function create(): Settings
    {
        return new Settings(
            prettyPrint: false,
            urlSafe: false,
        );
    }
}
