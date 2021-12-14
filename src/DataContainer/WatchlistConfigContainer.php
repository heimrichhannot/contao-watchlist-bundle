<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

class WatchlistConfigContainer
{
    /** @var ContaoFramework */
    protected $framework;
    /** @var DcaUtil */
    protected $dcaUtil;
    /** @var TwigTemplateLocator */
    protected $twigTemplateLocator;

    public function __construct(ContaoFramework $framework, DcaUtil $dcaUtil, TwigTemplateLocator $twigTemplateLocator)
    {
        $this->dcaUtil = $dcaUtil;
        $this->twigTemplateLocator = $twigTemplateLocator;
        $this->framework = $framework;
    }

    /**
     * Callback(table="tl_watchlist_config", target="config.onsubmit")
     */
    public function setDateAdded(DataContainer $dc)
    {
        $this->dcaUtil->setDateAdded($dc);
    }

    /**
     * Callback(table="tl_watchlist_config", target="config.oncopy")
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $this->dcaUtil->setDateAddedOnCopy($insertId, $dc);
    }

    /**
     * Callback(table="tl_watchlist_config", target="fields.insertTagAddItemTemplate.options")
     */
    public function getInsertTagAddItemTemplates(DataContainer $dc)
    {
        return $this->twigTemplateLocator->getPrefixedFiles(
            '_watchlist_insert_tag_add_item_'
        );
    }

    /**
     * Callback(table="tl_watchlist_config", target="fields.watchlistContentTemplate.options")
     */
    public function getWatchlistContentTemplates(DataContainer $dc)
    {
        return $this->framework->getAdapter(Controller::class)->getTemplateGroup('watchlist_content_');
    }
}
