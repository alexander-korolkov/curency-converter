<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CacheWarmupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('App\Service\CurrenciesListFetcherService')) {
            $definition = $container->findDefinition('App\Service\CurrenciesListFetcherService');
            $definition->addMethodCall('fetchCurrenciesList');
        }

        if ($container->has('App\Service\CurrencyRateFetcherService')) {
            $definition = $container->findDefinition('App\Service\CurrencyRateFetcherService');
            $definition->addMethodCall('fetchAllRates');
        }
    }
}
