<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

class WatchlistContainer
{
    /** @var DcaUtil */
    protected $dcaUtil;
    /** @var ModelInstanceChoice */
    protected $modelInstanceChoice;

    public function __construct(DcaUtil $dcaUtil, ModelInstanceChoice $modelInstanceChoice)
    {
        $this->dcaUtil = $dcaUtil;
        $this->modelInstanceChoice = $modelInstanceChoice;
    }

    /**
     * Callback(table="tl_watchlist", target="config.onsubmit")
     */
    public function setDateAdded(DataContainer $dc)
    {
        $this->dcaUtil->setDateAdded($dc);
    }

    /**
     * Callback(table="tl_watchlist", target="config.oncopy")
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $this->dcaUtil->setDateAddedOnCopy($insertId, $dc);
    }

    /**
     * Callback(table="tl_watchlist", target="fields.config.options")
     */
    public function getWatchlistConfigs(DataContainer $dc)
    {
        return $this->modelInstanceChoice->getChoices([
            'dataContainer' => 'tl_watchlist_config',
        ]);
    }
}
