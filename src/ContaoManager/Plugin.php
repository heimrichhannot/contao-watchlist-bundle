<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 19.03.18
 * Time: 10:48
 */

namespace HeimrichHannot\WatchlistBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use HeimrichHannot\AjaxBundle\HeimrichHannotContaoAjaxBundle;
use HeimrichHannot\WatchlistBundle\HeimrichHannotContaoWatchlistBundle;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoWatchlistBundle::class)->setLoadAfter([ContaoCoreBundle::class, HeimrichHannotContaoAjaxBundle::class]),
        ];
    }
    
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container) {
        
        $extensionConfigs = ContainerUtil::mergeConfigFile(
            'huh_list',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/heimrichhannot/contao-watchlist-bundle/src/Resources/config/config_list.yml'
        );


        return ContainerUtil::mergeConfigFile(
            'huh_encore',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/heimrichhannot/contao-watchlist-bundle/src/Resources/config/config_encore.yml'
        );
    }
}