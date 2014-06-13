<?php

namespace Zenstruck\Queue\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zenstruck\Queue\Bundle\DependencyInjection\Compiler\ConsumerTagCompilerPass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckQueueBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConsumerTagCompilerPass());
    }
}
