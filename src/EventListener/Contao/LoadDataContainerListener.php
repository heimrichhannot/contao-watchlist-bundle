<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener\Contao;

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

    public function __invoke($table): void
    {
        switch ($table) {
            case 'tl_watchlist':
                $this->prepareWatchlistDca();

                break;
        }
    }

    public function prepareWatchlistDca(): void
    {
        $this->dcaUtil->addAuthorFieldAndCallback('tl_watchlist');
    }
}
