<?php

namespace Lamudi\UseCaseBundle\DependencyInjection;

use Lamudi\UseCaseBundle\Annotation\UseCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LamudiUseCaseExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['defaults'])) {
            $defaultUseCaseConfig = new UseCase($config['defaults']);

            if ($defaultUseCaseConfig->getInputType()) {
                $container->setParameter('lamudi_angi_client.default_input_type', $defaultUseCaseConfig->getInputType());
                $container->setParameter('lamudi_angi_client.default_input_options', $defaultUseCaseConfig->getInputOptions());
            }
            if ($defaultUseCaseConfig->getOutputType()) {
                $container->setParameter('lamudi_angi_client.default_output_type', $defaultUseCaseConfig->getOutputType());
                $container->setParameter('lamudi_angi_client.default_output_options', $defaultUseCaseConfig->getOutputOptions());
            }
        }
    }
}
