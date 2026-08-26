<?php

declare(strict_types=1);

namespace Medas\Json;

use Medas\Core\{
    AttributeChecker,
    Attributes\DataHolder,
    Attributes\PreferredDefault,
    Attributes\Service,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Uuid,
    Serializers\PhpSerializer
};

#[Service]
readonly class ObjectsNormalizer
{
    private Serializer $serializer;

    public function __construct(
        #[PreferredDefault('Medas\ObjectToArraySerializer\ObjectToArraySerializer')]
        Serializer|null          $serializer,
        private AttributeChecker $attributeChecker,
    )
    {
        $this->serializer = $serializer === null ? new PhpSerializer() : $serializer;
    }

    public function normalize(mixed $value): mixed
    {
        if (is_object($value)) {
            if ($value instanceof HasId) {
                return (string) $value->id();
            }

            if ($value instanceof Uuid) {
                return (string) $value;
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format(DATE_ATOM);
            }

            if ($this->attributeChecker->hasAttribute($value, DataHolder::class)) {
                return $this->normalize($this->serializer->serialize($value));
            }

            if (!$value instanceof \BackedEnum && !is_iterable($value)) {
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
