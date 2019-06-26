<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle;

use HeimrichHannot\WatchlistBundle\DependencyInjection\Compiler\WatchlistPass;
use HeimrichHannot\WatchlistBundle\DependencyInjection\HeimrichHannotContaoWatchlistExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoWatchlistBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new HeimrichHannotContaoWatchlistExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new WatchlistPass());
    }


}
