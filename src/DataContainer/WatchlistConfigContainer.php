<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
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
        if ($this->container->get('huh.utils.container')->isBundleActive('submissions')) {
            Controller::loadLanguageFile('tl_submission_archive');
            $dca = &$GLOBALS['TL_DCA']['tl_watchlist_config'];
            $dca['palettes']['default'] = str_replace('useDownloadLink', 'useDownloadLink,downloadLinkUseNotification', $dca['palettes']['default']);
        }
    }

    public function getFields(DataContainer $dc): array
    {
        if (!($dataContainer = $dc->activeRecord->skipItemsDataContainer)) {
            return [];
        }

        return System::getContainer()->get('huh.utils.dca')->getFields($dataContainer);
    }
}
