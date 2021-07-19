<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "frontend"})
 */
class AjaxController
{
    const WATCHLIST_ITEM_URI = '/_huh_watchlist_item';

    protected ContaoFramework $framework;
    protected DatabaseUtil    $databaseUtil;
    protected WatchlistUtil   $watchlistUtil;

    public function __construct(
        ContaoFramework $framework,
        WatchlistUtil $watchlistUtil,
        DatabaseUtil $databaseUtil
    ) {
        $this->databaseUtil = $databaseUtil;
        $this->framework = $framework;
        $this->watchlistUtil = $watchlistUtil;
    }

    /**
     * @return Response
     *
     * @Route("/_huh_watchlist_item/{id}")
     */
    public function watchlistItemAction(Request $request, int $id = 0)
    {
        $this->framework->initialize();

        switch ($request->getMethod()) {
            case Request::METHOD_GET:
                $watchlist = $this->watchlistUtil->getCurrentWatchlist();

                // no id set -> list mode
                if (!$id) {
                    if (!$watchlist) {
                        return new Response('No watchlist found.', 404);
                    }

                    $items = $this->watchlistUtil->getWatchlistItems((int) $watchlist->id);

                    return new JsonResponse($items);
                }
                    // id set -> item mode
                    $item = $this->watchlistUtil->getWatchlistItem($id, (int) $watchlist->id);

                    if (null === $item) {
                        return new Response('No item with ID '.$id.' could be found.', 404);
                    }

                    return new JsonResponse($item->row());

            case Request::METHOD_POST:
                break;

            case Request::METHOD_PUT:
                break;

            case Request::METHOD_DELETE:
                break;

            default:
                return new Response('Method not allowed', 405);
        }
    }

    public function readWatchlistItem()
    {
    }
}
