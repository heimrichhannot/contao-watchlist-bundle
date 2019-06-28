<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\DataContainer;


use Contao\Controller;
use Contao\DC_Table;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WatchlistConfigContainer
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function onLoadCallback(DC_Table $dcTable)
    {
        if ($this->container->get('huh.utils.container')->isBundleActive('submissions'))
        {
            Controller::loadLanguageFile('tl_submission_archive');
            $dca = &$GLOBALS['TL_DCA']['tl_watchlist_config'];
            $dca['palettes']['default'] = str_replace('useDownloadLink', 'useDownloadLink,downloadLinkUseNotification', $dca['palettes']['default']);
        }
        return;
    }
}