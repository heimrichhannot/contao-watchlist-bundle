<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "frontend"})
 */
class AjaxController
{
    const WATCHLIST_CONTENT_URI = '/_huh_watchlist/content';
    const WATCHLIST_ITEM_URI = '/_huh_watchlist/item';

    protected ContaoFramework  $framework;
    protected DatabaseUtil     $databaseUtil;
    protected WatchlistUtil    $watchlistUtil;
    protected ModelUtil        $modelUtil;
    protected SessionInterface $session;

    public function __construct(
        ContaoFramework $framework,
        WatchlistUtil $watchlistUtil,
        DatabaseUtil $databaseUtil,
        ModelUtil $modelUtil,
        SessionInterface $session
    ) {
        $this->databaseUtil = $databaseUtil;
        $this->framework = $framework;
        $this->watchlistUtil = $watchlistUtil;
        $this->modelUtil = $modelUtil;
        $this->session = $session;
    }

    /**
     * @return Response
     *
     * @Route("/_huh_watchlist/content")
     */
    public function watchlistContentAction(Request $request)
    {
        $this->framework->initialize();

        switch ($request->getMethod()) {
            case Request::METHOD_GET:
                $rootPage = $request->get('root_page');

                $config = $this->watchlistUtil->getCurrentWatchlistConfig($rootPage);

                if (null === $config) {
                    return new Response('No watchlist config found. Please set it in your page root.', 500);
                }

                $template = new FrontendTemplate($config->watchlistContentTemplate ?: 'watchlist_content_default');

                $watchlist = $this->watchlistUtil->getCurrentWatchlist([
                    'rootPage' => $rootPage,
                ]);

                return new Response($this->watchlistUtil->parseWatchlistContent($template, $rootPage, $watchlist));

            default:
                return new Response('Method not allowed', 405);
        }
    }

    /**
     * @return Response
     *
     * @Route("/_huh_watchlist/download")
     */
    public function watchlistDownloadAction(Request $request)
    {
        $this->framework->initialize();

        switch ($request->getMethod()) {
            case Request::METHOD_GET:
                break;

            default:
                return new Response('Method not allowed', 405);
        }
    }

    /**
     * @return Response
     *
     * @Route("/_huh_watchlist/item")
     */
    public function watchlistItemAction(Request $request)
    {
        $this->framework->initialize();

        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                // create/delete (couldn't use the DELETE method because it can't have a payload which is necessary to check whether a
                // DELETE is permitted in the current context)
                $data = json_decode($request->getContent(), true);
                $cleanedData = [];
                $rootPage = $data['rootPage'];
                $deleteMode = $data['delete'];

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

                if (isset($data['file'])) {
                    $data['file'] = StringUtil::uuidToBin($data['file']);
                }

                if ($deleteMode) {
                    if (null === ($item = $this->watchlistUtil->getWatchlistItemByData($data, $watchlist->id))) {
                        return new Response('Watchlist item with the given id couldn\'t be found.', 404);
                    }

                    $result = $this->databaseUtil->delete('tl_watchlist_item', 'tl_watchlist_item.id=?', [$item->id]);

                    if ($result->affectedRows > 0) {
                        return new Response('Watchlist item deleted successfully.');
                    }

                    return new Response('Error deleting watchlist item.', 500);
                }

                // create mode
                // already existing?
                if (null !== $this->watchlistUtil->getWatchlistItemByData($data, $watchlist->id)) {
                    return new Response($GLOBALS['TL_LANG']['MSC']['watchlistBundle']['itemAlreadyInCurrentWatchlist'], 409);
                }

                switch ($data['type']) {
                    case WatchlistItemContainer::TYPE_FILE:
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

            default:
                return new Response('Method not allowed', 405);
        }
    }
}
