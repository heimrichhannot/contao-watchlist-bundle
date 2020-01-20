<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\FrontendModule;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Model\Collection;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\Request\Request;
use HeimrichHannot\WatchlistBundle\FrontendFramework\WatchlistFrameworkInterface;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\DownloadAllActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;
use HeimrichHannot\WatchlistBundle\PartialTemplate\OpenWatchlistWindowActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\WatchlistWindowPartialTemplate;
use Patchwork\Utf8;
use Psr\Container\ContainerInterface;

/**
 * Class ModuleWatchlist
 * @package HeimrichHannot\WatchlistBundle\Module
 */
class ModuleWatchlist extends Module
{
    const MODULE_WATCHLIST = 'huhwatchlist';

    protected $strTemplate = 'mod_watchlist';

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ModuleModel $objModule)
    {
        $this->container = System::getContainer();

        parent::__construct($objModule);
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['watchlist'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (!$this->container->get('huh.watchlist.watchlist_manager')->checkPermission($this)) {
            return;
        }

        if (Request::getGet('file')) {
            Controller::sendFileToBrowser(Request::getGet('file'));
        }

        return parent::generate();
    }

    protected function compile()
    {
        $configuration = WatchlistConfigModel::findByPk($this->watchlistConfig);
        if (null === $configuration)
        {
            $this->Template->toggler = '<div style="background-color: red; color: white;">No watchlist config set.</div>';
            return;
        }

        $watchlist = $this->container->get('huh.watchlist.watchlist_manager')->getWatchlistModel($configuration);

        if($watchlist instanceof Collection) {
            $watchlist = $watchlist[0];
        }

        $watchlistContainerId = 'huh_watchlist_window_'.$this->id.'_'.$configuration->id.'_'.rand(0,99999);

        $this->Template->watchlistContainerId = $watchlistContainerId;
        $this->Template->watchlistId = $watchlist->id;

        $this->Template->watchlistContainerCssClass = 'watchlist-'.$watchlist->id;

        $this->Template->toggler = $this->container->get(PartialTemplateBuilder::class)->generate(new OpenWatchlistWindowActionPartialTemplate($configuration, $watchlist, $watchlistContainerId));

        $this->Template->watchlistWindow = $this->container->get(PartialTemplateBuilder::class)->generate(
            new WatchlistWindowPartialTemplate($configuration, $watchlist->id, null, ['watchlistContainerId' => $watchlistContainerId])
        );

        if ($this->useGlobalDownloadAllAction) {
            $this->Template->downloadAllAction = $this->container->get(PartialTemplateBuilder::class)->generate(
                new DownloadAllActionPartialTemplate($configuration, $watchlist)
            );
        }

        /** @var WatchlistFrameworkInterface $framework */
        $framework = $this->container->get('huh.watchlist.manager.frontend_frameworks')->getFrameworkByType($configuration->watchlistFrontendFramework);
        $this->Template->setData($framework->prepareModuleTemplate($this->Template->getData(), $this));
    }
}
