<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

/**
 * Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    /** @var DcaUtil  */
    protected $dcaUtil;

    public function __construct(DcaUtil $dcaUtil)
    {
        $this->dcaUtil = $dcaUtil;
    }

    public function __invoke($table)
    {
        switch ($table) {
            case 'tl_watchlist':
                $this->prepareWatchlistDca();

                break;

            case 'tl_list_config':
                $this->prepareListConfigDcaDca();

                break;

            case 'tl_reader_config':
                $this->prepareReaderConfigDcaDca();

                break;
        }
    }

    public function prepareWatchlistDca()
    {
        $this->dcaUtil->addAuthorFieldAndCallback('tl_watchlist');
    }

    public function prepareListConfigDcaDca()
    {
        \HeimrichHannot\ListBundle\Backend\ListConfig::addOverridableFields();
    }

    public function prepareReaderConfigDcaDca()
    {
        \HeimrichHannot\ReaderBundle\Backend\ReaderConfig::addOverridableFields();
    }
}
