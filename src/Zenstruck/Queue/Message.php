<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Message
{
    private $data;
    private $info;
    private $metadata;
    private $delay;

    /**
     * @param mixed    $data     The data
     * @param string   $info     Info text
     * @param array    $metadata Additional data
     * @param int|null $delay    The delay in seconds (null for default)
     */
    public function __construct($data, $info, array $metadata, $delay = null)
    {
        $this->data = $data;
        $this->info = $info;
        $this->metadata = $metadata;
        $this->delay = $delay;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return int|null
     */
    public function getDelay()
    {
        return $this->delay;
    }
}
