<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

class WatchlistConfigContainer
{
    protected DcaUtil             $dcaUtil;
    protected TwigTemplateLocator $twigTemplateLocator;

    public function __construct(DcaUtil $dcaUtil, TwigTemplateLocator $twigTemplateLocator)
    {
        $this->dcaUtil = $dcaUtil;
        $this->twigTemplateLocator = $twigTemplateLocator;
    }

    /**
     * @Callback(table="tl_watchlist_config", target="config.onsubmit")
     */
    public function setDateAdded(DataContainer $dc)
    {
        $this->dcaUtil->setDateAdded($dc);
    }

    /**
     * @Callback(table="tl_watchlist_config", target="config.oncopy")
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $this->dcaUtil->setDateAddedOnCopy($insertId, $dc);
    }

    /**
     * @Callback(table="tl_watchlist_config", target="fields.insertTagAddItemTemplate.options")
     */
    public function getInsertTagAddItemTemplates(DataContainer $dc)
    {
        return $this->twigTemplateLocator->getPrefixedFiles(
            '_watchlist_insert_tag_add_item_'
        );
    }

    /**
     * @Callback(table="tl_watchlist_config", target="fields.insertTagDeleteItemTemplate.options")
     */
    public function getInsertTagDeleteItemTemplates(DataContainer $dc)
    {
        return $this->twigTemplateLocator->getPrefixedFiles(
            '_watchlist_insert_tag_delete_item_'
        );
    }
}
