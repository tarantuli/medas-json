<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\{
    AttributeChecker,
    Attributes\DataHolder,
    Attributes\PreferredDefault,
    Attributes\Service,
    Interfaces\Serializer,
    Interfaces\Uuid
};

#[Service]
readonly class ObjectsNormalizer
{
    public function __construct(
        #[PreferredDefault('Medas\ObjectToArraySerializer\ObjectToArraySerializer')]
        private Serializer       $serializer,
        private AttributeChecker $attributeChecker,
    )
    {
    }

    public function normalize(mixed $value): mixed
    {
        if (is_object($value)) {
            if ($value instanceof Uuid) {
                return (string) $value;
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format(DATE_ATOM);
            }

            if ($this->attributeChecker->hasAttribute($value, DataHolder::class)) {
                return $this->normalize($this->serializer->serialize($value));
            }

            if (!$value instanceof \BackedEnum) {
                throw new Exceptions\ObjectFoundInValue($value);
            }
        }

        if (!is_iterable($value)) {
            return $value;
        }

        $normalizedValues = [];

        foreach ($value as $key => $subValue) {
            $normalizedValues[$key] = $this->normalize($subValue);
        }

        return $normalizedValues;
    }
}
