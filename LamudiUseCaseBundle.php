<?php

namespace Lamudi\UseCaseBundle;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Lamudi\UseCaseBundle\DependencyInjection\UseCaseCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LamudiUseCaseBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/UseCase.php');
    }

    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UseCaseCompilerPass());
    }
}
