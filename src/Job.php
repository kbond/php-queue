<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Job implements \JsonSerializable
{
    private $payload;
    private $attempts;
    private $id;
    private $failedException;

    /**
     * @param Payload         $payload
     * @param int             $attempts
     * @param string|int|null $id
     */
    public function __construct(Payload $payload, $attempts = 1, $id = null)
    {
        $this->payload = $payload;
        $this->attempts = $attempts;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function serializedEnvelope()
    {
        return $this->payload->serializedEnvelope();
    }

    /**
     * @return string
     */
    public function metadata()
    {
        return $this->payload->metadata();
    }

    /**
     * @return int
     */
    public function attempts()
    {
        return $this->attempts;
    }

    /**
     * @return int|null|string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param \Exception $exception
     */
    public function fail(\Exception $exception)
    {
        $this->failedException = $exception;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->failedException instanceof \Exception;
    }

    /**
     * @return \Exception|null
     */
    public function failedException()
    {
        return $this->failedException;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'metadata' => $this->metadata(),
            'id' => $this->id(),
            'failed' => $this->isFailed(),
            'attempts' => $this->attempts(),
        ];
    }
}
