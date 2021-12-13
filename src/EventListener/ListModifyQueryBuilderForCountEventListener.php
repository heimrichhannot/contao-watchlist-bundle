<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener;

use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderForCountEvent;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * ServiceTag("kernel.event_listener", event="huh.list.event.list_modify_query_builder_for_count")
 */
class ListModifyQueryBuilderForCountEventListener
{
    /** @var ModelUtil */
    protected $modelUtil;
    /** @var Request */
    protected $request;
    /** @var DatabaseUtil */
    protected $databaseUtil;
    /** @var WatchlistUtil */
    protected $watchlistUtil;

    public function __construct(ModelUtil $modelUtil, Request $request, DatabaseUtil $databaseUtil, WatchlistUtil $watchlistUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->request = $request;
        $this->databaseUtil = $databaseUtil;
        $this->watchlistUtil = $watchlistUtil;
    }

    public function __invoke(ListModifyQueryBuilderForCountEvent $event): void
    {
        $qp = $event->getQueryBuilder();
        $listConfig = $event->getListConfig();

        if (!$listConfig->actAsWatchlistShareTarget) {
            return;
        }

        if (!($watchlistUuid = $this->request->getGet('watchlist')) ||
            null === ($filter = $this->modelUtil->findModelInstanceByPk('tl_filter_config', $listConfig->filter))) {
            // hide any items if for security reasons
            $qp->andWhere($qp->expr()->eq(1, 0));

            return;
        }

        if (null === ($watchlist = $this->databaseUtil->findOneResultBy('tl_watchlist', ['tl_watchlist.uuid=?'], [$watchlistUuid])) || $watchlist->numRows < 1) {
            // hide any items if for security reasons
            $qp->andWhere($qp->expr()->eq(1, 0));

            return;
        }

        $table = $filter->dataContainer;

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
