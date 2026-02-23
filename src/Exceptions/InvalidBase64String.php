<?php

declare(strict_types=1);

namespace Medas\Json\Exceptions;

use Medas\Core\Exceptions\BaseException;

class InvalidBase64String extends BaseException
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public function pattern(): string
    {
        return 'invalid base64 protected string: %s';
    }
}
