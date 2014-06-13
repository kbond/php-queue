<?php

namespace Zenstruck\Queue\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zenstruck_queue');

        $rootNode
            ->children()
                ->booleanNode('logging')
                    ->defaultTrue()
                    ->info('Enable logging for the queue')
                ->end()
                ->booleanNode('spool')
                    ->defaultTrue()
                    ->info('When true, pushes to the queue are spooled and flushed on Response/Console terminate')
                ->end()
                ->booleanNode('fail_unknown')
                    ->defaultFalse()
                    ->info('When true, jobs that aren\'t specifically failed, requeued or deleted will fail')
                ->end()
                ->arrayNode('adapter')
                    ->canBeUnset()
                    ->performNoDeepMerging()
                    ->children()
                        ->booleanNode('synchronous')->end()
                        ->arrayNode('amazon_sqs')
                            ->children()
                                ->scalarNode('amazon_sqs_id')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('queue_url')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('redis')
                            ->children()
                                ->scalarNode('redis_client_id')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('queue_name')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('beanstalkd')
                            ->children()
                                ->scalarNode('beanstalkd_client_id')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('tube')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
