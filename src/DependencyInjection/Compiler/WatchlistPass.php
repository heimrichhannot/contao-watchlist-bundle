<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WatchlistPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('huh.watchlist.manager.frontend_frameworks')) {
            return;
        }

        $definition = $container->findDefinition('huh.watchlist.manager.frontend_frameworks');

        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('huh.watchlist.framework');

        foreach ($taggedServices as $id => $tags) {
            // add the transport service to the TransportChain service
            $definition->addMethodCall('addFramework', [new Reference($id)]);
        }
    }
}
