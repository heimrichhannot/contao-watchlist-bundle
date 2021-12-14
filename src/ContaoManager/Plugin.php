<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use HeimrichHannot\WatchlistBundle\HeimrichHannotWatchlistBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface, RoutingPluginInterface
{
    /**
     * Gets a list of autoload configurations for this bundle.
     *
     * @return ConfigInterface[]
     */
    public function getBundles(ParserInterface $parser)
    {
        return [BundleConfig::create(HeimrichHannotWatchlistBundle::class)->setLoadAfter([
            'HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle',
            'HeimrichHannot\ReaderBundle\HeimrichHannotContaoReaderBundle',
            ContaoCoreBundle::class,
        ])];
    }

    /**
     * Allows a plugin to load container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotWatchlistBundle/Resources/config/services.yml');

        if (class_exists('HeimrichHannot\EncoreBundle\HeimrichHannotContaoEncoreBundle')) {
            $loader->load('@HeimrichHannotWatchlistBundle/Resources/config/config_encore.yml');
        }

        if (class_exists('Contao\CoreBundle\Migration\AbstractMigration')) {
            $loader->load('@HeimrichHannotWatchlistBundle/Resources/config/services_migration.yml');
        }
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        $file = __DIR__.'/../Resources/config/routing.yml';

        return $resolver->resolve($file)->load($file);
    }
}
