<?php

namespace Zenstruck\Queue\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ChainExitStrategy implements ExitStrategy
{
    private $reason;

    /** @var array|ExitStrategy[] */
    private $exitStrategies = [];

    /**
     * @param array|ExitStrategy[] $exitStrategies
     */
    public function __construct(array $exitStrategies = [])
    {
        foreach ($exitStrategies as $exitStrategy) {
            $this->addExitStrategy($exitStrategy);
        }
    }

    public function addExitStrategy(ExitStrategy $exitStrategy)
    {
        $this->exitStrategies[] = $exitStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldExit($count)
    {
        foreach ($this->exitStrategies as $exitStrategy) {
            if ($exitStrategy->shouldExit($count)) {
                $this->reason = $exitStrategy->getReason();

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return $this->reason;
    }
}
