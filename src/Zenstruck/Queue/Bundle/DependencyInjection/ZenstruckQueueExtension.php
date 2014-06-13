<?php

namespace Zenstruck\Queue\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ZenstruckQueueExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('queue.xml');
        $loader->load('adapters.xml');

        $container->setParameter('zenstruck_queue.fail_unknown', $mergedConfig['fail_unknown']);

        if ($mergedConfig['logging']) {
            $loader->load('logging.xml');
        }

        if ($mergedConfig['spool']) {
            $loader->load('spool.xml');
        }

        if (!isset($mergedConfig['adapter'])) {
            throw new InvalidConfigurationException('Queue adapter is not set.');
        }

        $adapterConfig = $mergedConfig['adapter'];

        if (count($adapterConfig) > 1) {
            throw new InvalidConfigurationException('Only 1 queue adapter can be configured at a time.');
        }

        reset($adapterConfig);
        $adapter = key($adapterConfig);
        $adapterConfig = $adapterConfig[$adapter];

        $adapterId = 'zenstruck_queue.adapter';

        // configure adapter
        switch ($adapter) {
            case 'synchronous':
                $container->setDefinition($adapterId, new DefinitionDecorator('zenstruck_queue.adapter.synchronous'));

                break;

            case 'amazon_sqs':
                $container->setDefinition($adapterId, new DefinitionDecorator('zenstruck_queue.adapter.amazon_sqs'))
                    ->addArgument(new Reference($adapterConfig['amazon_sqs_id']))
                    ->addArgument($adapterConfig['queue_url'])
                ;

                break;

            case 'redis':
                $container->setDefinition($adapterId, new DefinitionDecorator('zenstruck_queue.adapter.redis'))
                    ->addArgument(new Reference($adapterConfig['redis_client_id']))
                    ->addArgument($adapterConfig['queue_name'])
                ;

                break;

            case 'beanstalkd':
                $container->setDefinition($adapterId, new DefinitionDecorator('zenstruck_queue.adapter.beanstalkd'))
                    ->addArgument(new Reference($adapterConfig['beanstalkd_client_id']))
                    ->addArgument($adapterConfig['tube'])
                ;

                break;

            default:
                throw new InvalidConfigurationException(sprintf('"%s" is an unrecognized adapter.', $adapter));
        }
    }
}
