<?php

namespace Zenstruck\Queue;

use Assert\AssertionFailedException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Payload implements \JsonSerializable
{
    private $serializedEnvelope;
    private $metadata;

    /**
     * @param string $json
     *
     * @return Payload|null
     */
    public static function fromJson($json)
    {
        if (!is_scalar($json)) {
            return null;
        }

        $decodedData = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        if (!is_array($decodedData)) {
            return null;
        }

        return self::fromArray($decodedData);
    }

    /**
     * @param array $array
     *
     * @return Payload|null
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['serialized_envelope']) || !isset($array['metadata'])) {
            return null;
        }

        try {
            \Assert\that($array['serialized_envelope'])->string();
            \Assert\that($array['metadata'])->string();
        } catch (AssertionFailedException $e) {
            return null;
        }

        return new self($array['serialized_envelope'], $array['metadata']);
    }

    /**
     * @param string $serializedEnvelope
     * @param string $metadata
     */
    public function __construct($serializedEnvelope, $metadata)
    {
        $this->serializedEnvelope = $serializedEnvelope;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function serializedEnvelope()
    {
        return $this->serializedEnvelope;
    }

    /**
     * @return string
     */
    public function metadata()
    {
        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'serialized_envelope' => $this->serializedEnvelope,
            'metadata' => $this->metadata,
        ];
    }
}
