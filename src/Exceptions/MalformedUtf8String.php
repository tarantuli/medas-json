<?php

declare(strict_types=1);

namespace Medas\Json\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MalformedUtf8String extends BaseException
{
    public function __construct(int $offset, string $string)
    {
        parent::__construct($offset, $string);
    }

    public function pattern(): string
    {
        return 'malformed utf8 string after protecting at offset %s: %s';
    }
}
