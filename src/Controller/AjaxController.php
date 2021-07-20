<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
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
    protected ModelUtil       $modelUtil;

    public function __construct(
        ContaoFramework $framework,
        WatchlistUtil $watchlistUtil,
        DatabaseUtil $databaseUtil,
        ModelUtil $modelUtil
    ) {
        $this->databaseUtil = $databaseUtil;
        $this->framework = $framework;
        $this->watchlistUtil = $watchlistUtil;
        $this->modelUtil = $modelUtil;
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
                // read
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
                // create
                $data = json_decode($request->getContent(), true);
                $cleanedData = [];
                $rootPage = $data['rootPage'];

                // clean user input (avoid injection)
                $db = Database::getInstance();

                foreach ($data as $field => $value) {
                    if (!$db->fieldExists($field, 'tl_watchlist_item')) {
                        continue;
                    }

                    $cleanedData[$field] = $value;
                }

                $data = $cleanedData;

                // get or create watchlist
                if (isset($data['pid'])) {
                    $watchlist = $this->databaseUtil->findOneResultBy('tl_watchlist', ['tl_watchlist.uuid=?'], [$data['pid']]);

                    if ($watchlist->numRows < 1) {
                        $watchlist = null;
                    }
                } else {
                    $watchlist = $this->watchlistUtil->getCurrentWatchlist([
                        'rootPage' => $rootPage,
                        'createIfNotExisting' => true,
                    ]);
                }

                // watchlist creation failed
                if (null === $watchlist) {
                    return new Response('A watchlist couldn\'t be created or found.', 500);
                }

                $data['pid'] = $watchlist->id;

                switch ($data['type']) {
                    case WatchlistItemContainer::TYPE_FILE:
                        $data['file'] = StringUtil::uuidToBin($data['file']);

                        // already existing?
                        $existingItem = $this->databaseUtil->findOneResultBy('tl_watchlist_item',
                            ['tl_watchlist_item.type=?', 'tl_watchlist_item.pid=?', 'tl_watchlist_item.file=UNHEX(?)'],
                            [WatchlistItemContainer::TYPE_FILE, $data['pid'], bin2hex($data['file'])]
                        );

                        if ($existingItem->numRows > 0) {
                            return new Response($GLOBALS['TL_LANG']['MSC']['watchlistBundle']['itemAlreadyInCurrentWatchlist'], 409);
                        }

                        if (null === ($fileModel = $this->modelUtil->callModelMethod('tl_files', 'findByUuid', $data['file']))) {
                            return new Response('File with the given uuid couldn\'t be found.', 404);
                        }

                        // get title from file
                        if (!isset($data['title']) || \in_array($data['title'], ['null', 'NULL', "''", '0'])) {
                            // filename is the fallback
                            $data['title'] = $fileModel->name;

                            // translate
                            $meta = StringUtil::deserialize($fileModel->meta, true);

                            if (isset($meta[$GLOBALS['TL_LANGUAGE']]['title'])) {
                                $data['title'] = $meta[$GLOBALS['TL_LANGUAGE']['title']];
                            }
                        }

                        $result = $this->watchlistUtil->addItemToWatchlist(
                            $data, $data['pid']
                        );

                        if (null === $result) {
                            return new Response('Error while adding the item to watchlist.');
                        }

                        return new Response('Item successfully added.');

                    case WatchlistItemContainer::TYPE_ENTITY:
                        // already existing?
                        $existingItem = $this->databaseUtil->findOneResultBy('tl_watchlist_item',
                            ['tl_watchlist_item.type=?', 'tl_watchlist_item.pid=?', 'tl_watchlist_item.entityTable=?', 'tl_watchlist_item.entity=?'],
                            [WatchlistItemContainer::TYPE_ENTITY, $data['pid'], $data['entityTable'], $data['entity']]
                        );

                        if ($existingItem->numRows > 0) {
                            return new Response('A watchlist item of this entity is already existing in the current watchlist.', 409);
                        }

                        if (null === $this->modelUtil->findModelInstanceByPk($data['entityTable'], $data['entity'])) {
                            return new Response('Entity with the given id couldn\'t be found in the given table.', 404);
                        }

                        $result = $this->watchlistUtil->addItemToWatchlist(
                            $data, $data['pid']
                        );

                        if (null === $result) {
                            return new Response('Error while adding the item to watchlist.');
                        }

                        return new Response('Item successfully added.');
                }

                return new Response('Watchlist item type unsupported.', 500);

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
