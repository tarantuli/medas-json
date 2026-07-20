<?php

declare(strict_types=1);

namespace Medas\Json\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MalformedUtf8String extends BaseException
{
    private const int FRAGMENT_LENGTH = 30;

    public function __construct(int $offset, string $string)
    {
        $prefix = substr($string, $offset - self::FRAGMENT_LENGTH, self::FRAGMENT_LENGTH);
        $fragment = substr($string, $offset, self::FRAGMENT_LENGTH);

        parent::__construct($offset, $prefix, $fragment);
    }

    public function pattern(): string
    {
        return 'malformed utf8 string after protecting at offset %s after %s: %s';
    }
}
