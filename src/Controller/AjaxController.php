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
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "frontend"})
 */
class AjaxController
{
    const WATCHLIST_URI = '/_huh_watchlist';
    const WATCHLIST_DOWNLOAD_ALL_URI = '/_huh_watchlist/download_all';
    const WATCHLIST_CONTENT_URI = '/_huh_watchlist/content';
    const WATCHLIST_ITEM_URI = '/_huh_watchlist/item';

    /** @var ContaoFramework */
    protected $framework;

    /** @var DatabaseUtil */
    protected $databaseUtil;

    /** @var WatchlistUtil */
    protected $watchlistUtil;

    /** @var ModelUtil */
    protected $modelUtil;

    /** @var SessionInterface */
    protected $session;

    /** @var ContainerInterface */
    protected $container;

    /** @var FileUtil */
    protected $fileUtil;

    public function __construct(
        ContainerInterface $container,
        ContaoFramework $framework,
        WatchlistUtil $watchlistUtil,
        DatabaseUtil $databaseUtil,
        ModelUtil $modelUtil,
        FileUtil $fileUtil,
        SessionInterface $session
    ) {
        $this->databaseUtil = $databaseUtil;
        $this->framework = $framework;
        $this->watchlistUtil = $watchlistUtil;
        $this->modelUtil = $modelUtil;
        $this->session = $session;
        $this->container = $container;
        $this->fileUtil = $fileUtil;
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
                $rootPage = $request->get('wl_root_page');
                $currentUrl = $request->get('wl_url');

                $config = $this->watchlistUtil->getCurrentWatchlistConfig($rootPage);

                if (null === $config) {
                    return new Response('No watchlist config found. Please set it in your page root.', 500);
                }

                $template = new FrontendTemplate($config->watchlistContentTemplate ?: 'watchlist_content_default');

                $watchlist = $this->watchlistUtil->getCurrentWatchlist([
                    'rootPage' => $rootPage,
                ]);

                return new Response($this->watchlistUtil->parseWatchlistContent($template, $currentUrl, $rootPage, $config, $watchlist));

            default:
                return new Response('Method not allowed', 405);
        }
    }

    /**
     * @return Response
     *
     * @Route("/_huh_watchlist")
     */
    public function watchlistAction(Request $request)
    {
        $this->framework->initialize();

        $rootPage = $request->get('wl_root_page');

        switch ($request->getMethod()) {
            case Request::METHOD_DELETE:
                $watchlist = $this->watchlistUtil->getCurrentWatchlist([
                    'rootPage' => $rootPage,
                ]);

                if (null === $watchlist) {
                    return new Response('No watchlist found.', 500);
                }

                $this->databaseUtil->delete('tl_watchlist_item', 'tl_watchlist_item.pid=?', [$watchlist->id]);
                $this->databaseUtil->delete('tl_watchlist', 'tl_watchlist.id=?', [$watchlist->id]);

                return new Response('Watchlist deleted successfully.');

            default:
                return new Response('Method not allowed', 405);
        }
    }

    /**
     * @return Response
     *
     * @Route("/_huh_watchlist/download_all")
     */
    public function watchlistDownloadAllAction(Request $request)
    {
        $this->framework->initialize();

        $rootPage = $request->get('wl_root_page');

        switch ($request->getMethod()) {
            case Request::METHOD_GET:
                $projectDir = $this->container->getParameter('kernel.project_dir');
                $watchlist = $this->watchlistUtil->getCurrentWatchlist([
                    'rootPage' => $rootPage,
                ]);

                if (null === $watchlist) {
                    return new Response('A watchlist couldn\'t be created or found.', 500);
                }

                // create zip file
                $files = [];

                foreach ($this->watchlistUtil->getWatchlistItems($watchlist->id) as $item) {
                    if (WatchlistItemContainer::TYPE_FILE !== $item['type'] || !($path = $this->fileUtil->getPathFromUuid($item['file']))) {
                        continue;
                    }

                    $files[] = $projectDir.'/'.$path;
                }

                // Create new Zip Archive.
                $zip = new \ZipArchive();

                // The name of the Zip documents.
                $zipName = 'watchlist.zip';

                $zip->open($zipName, \ZipArchive::CREATE);

                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }

                $zip->close();

                // send to browser
                $response = new Response(file_get_contents($zipName));
                $response->headers->set('Content-Type', 'application/zip');
                $response->headers->set('Content-Disposition', 'attachment;filename="'.$zipName.'"');
                $response->headers->set('Content-length', filesize($zipName));

                return $response;

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
                        if (!isset($data['title'])) {
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

                        if (isset($data['entityFile'])) {
                            $data['entityFile'] = StringUtil::uuidToBin($data['entityFile']);
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
