<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use HeimrichHannot\AjaxBundle\HeimrichHannotContaoAjaxBundle;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\WatchlistBundle\HeimrichHannotContaoWatchlistBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface, ConfigPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoWatchlistBundle::class)->setLoadAfter([
                ContaoCoreBundle::class,
                HeimrichHannotContaoAjaxBundle::class,
                'notification_center',
                'submissions',
                'formhybrid',
                'HeimrichHannot\TwigTemplatesBundle\ContaoTwigTemplatesBundle',
            ]),
        ];
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        $extensionConfigs = ContainerUtil::mergeConfigFile(
            'huh_watchlist',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config.yml'
        );

        $extensionConfigs = ContainerUtil::mergeConfigFile(
            'huh_list',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config_list.yml'
        );

        return ContainerUtil::mergeConfigFile(
            'huh_encore',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config_encore.yml'
        );
    }

    /**
     * Allows a plugin to load container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotContaoWatchlistBundle/Resources/config/services.yml');
        $loader->load('@HeimrichHannotContaoWatchlistBundle/Resources/config/datacontainers.yml');
        $loader->load('@HeimrichHannotContaoWatchlistBundle/Resources/config/listeners.yml');
    }
}
