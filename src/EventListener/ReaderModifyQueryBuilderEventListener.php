<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener;

use HeimrichHannot\ReaderBundle\Event\ReaderModifyQueryBuilderEvent;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="huh.reader.event.reader_modify_query_builder")
 */
class ReaderModifyQueryBuilderEventListener
{
    protected ModelUtil     $modelUtil;
    protected Request       $request;
    protected DatabaseUtil  $databaseUtil;
    protected WatchlistUtil $watchlistUtil;

    public function __construct(ModelUtil $modelUtil, Request $request, DatabaseUtil $databaseUtil, WatchlistUtil $watchlistUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->request = $request;
        $this->databaseUtil = $databaseUtil;
        $this->watchlistUtil = $watchlistUtil;
    }

    public function __invoke(ReaderModifyQueryBuilderEvent $event): void
    {
        $qp = $event->getQueryBuilder();
        $readerConfig = $event->getReaderConfig();

        if (!$readerConfig->actAsWatchlistShareTarget) {
            return;
        }

        if (!($watchlistUuid = $this->request->getGet('watchlist'))) {
            // hide any items if for security reasons
            $qp->andWhere($qp->expr()->eq(1, 0));

            return;
        }

        if (null === ($watchlist = $this->databaseUtil->findOneResultBy('tl_watchlist', ['tl_watchlist.uuid=?'], [$watchlistUuid])) || $watchlist->numRows < 1) {
            // hide any items if for security reasons
            $qp->andWhere($qp->expr()->eq(1, 0));

            return;
        }

        $table = $readerConfig->dataContainer;

        $items = $this->watchlistUtil->getWatchlistItems($watchlist->id);

        $ids = [];

        foreach ($items as $item) {
            if ($item['entityTable'] !== $table) {
                continue;
            }

            $ids[] = $item['entity'];
        }

        if (empty($ids)) {
            // hide any items if for security reasons
            $qp->andWhere($qp->expr()->eq(1, 0));
        } else {
            $qp->andWhere($qp->expr()->in("$table.id", $ids));
        }
    }
}
