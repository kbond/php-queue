<?php

namespace Zenstruck\Queue\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ConsumerTagCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('zenstruck_queue.queue')) {
            return;
        }

        $definition = $container->getDefinition('zenstruck_queue.queue');

        foreach ($container->findTaggedServiceIds('zenstruck_queue.consumer') as $id => $tags) {
            foreach ($tags as $attributes) {
                $priority = empty($attributes['priority']) ? 0 : $attributes['priority'];
                $definition->addMethodCall('addConsumer', array(new Reference($id), $priority));
            }
        }
    }
}
