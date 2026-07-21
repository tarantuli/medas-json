<?php

declare(strict_types=1);

namespace Medas\Json\Exceptions;

use Medas\Core\Exceptions\BaseException;

class ObjectFoundInValue extends BaseException
{
    public function __construct(object $object)
    {
        parent::__construct($object::class);
    }

    public function pattern(): string
    {
        return 'non-BackedEnum object found of class %s';
    }
}
