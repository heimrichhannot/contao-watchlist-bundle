<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemFactory;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemType;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route(defaults={"_scope" = "frontend"})
 */
class AjaxController extends AbstractController
{
    const WATCHLIST_URI = '/_huh_watchlist';
    const WATCHLIST_DOWNLOAD_ALL_URI = '/_huh_watchlist/download_all';
    const WATCHLIST_CONTENT_URI = '/_huh_watchlist/content';
    const WATCHLIST_ITEM_URI = '/_huh_watchlist/item';

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly WatchlistUtil $watchlistUtil,
        private readonly Utils $utils,
        private readonly string $projectDir,
        private readonly TranslatorInterface $translator,
        private readonly Connection $connection,
        private readonly WatchlistItemFactory $watchlistItemFactory,
    )
    {
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

                if ($rootPage && !$request->attributes->has('pageModel')) {
                    $page = PageModel::findByPk((int)$rootPage);

                    if ($page && $page->id == $rootPage) {
                        // needed to fix warning in contao:
                        if (!$page->trail) {
                            $page->trail = [];
                        }

                        // add page model to request and global to make it available in dependent code
                        $request->attributes->set('pageModel', $page);

                        if (!isset($GLOBALS['objPage'])) {
                            $GLOBALS['objPage'] = $page;
                        }
                    }
                }

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

                $this->connection->delete('tl_watchlist_item', ['pid' => $watchlist->id]);
                $watchlist->delete();

                return new Response('Watchlist deleted successfully.');

            default:
                return new Response('Method not allowed', 405);
        }
    }

    /**
     * @Route("/_huh_watchlist/download_all", name="huh_watchlist_downlad_all", methods={"GET"})
     */
    public function watchlistDownloadAllAction(Request $request): Response
    {
        $this->framework->initialize();

        $watchlist = null;

        if ($request->query->has('watchlist')) {
            $watchlistId = (int)$request->query->get('watchlist');
            $watchlist = $this->utils->model()->findModelInstanceByPk(WatchlistModel::getTable(), $watchlistId);
        }

        if (!$watchlist && $request->query->has('wl_root_page')) {
            $rootPageId = (int)$request->query->get('wl_root_page');
            $watchlist = $this->watchlistUtil->getCurrentWatchlist([
                'rootPage' => $rootPageId,
            ]);
        }

        if (null === $watchlist) {
            return new Response('A watchlist couldn\'t be created or found.', 500);
        }

        // create zip file
        $files = [];

        foreach ($this->watchlistUtil->getWatchlistItems($watchlist->id) as $item) {
            $wlItem = $this->watchlistItemFactory->build($item);
            if (WatchlistItemType::FILE !== $wlItem->getType() || !$wlItem->fileExist()) {
                continue;
            }

            $files[] = $this->projectDir.'/'.$wlItem->getFile()->getPath();
        }

        $cacheDir = sys_get_temp_dir() . '/huh_watchlist';

        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($cacheDir)) {
            $fileSystem->mkdir($cacheDir);
        }

        $hash = md5($watchlist->id . ' ' . implode(',', $files));
        $fileName = 'watchlist_' . $hash . '.zip';
        $filePath = $cacheDir . \DIRECTORY_SEPARATOR . $fileName;

        if (!$fileSystem->exists($filePath)) {
            // Create new Zip Archive.
            $zip = new \ZipArchive();

            $zip->open($filePath, \ZipArchive::CREATE);

            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();
        }

        // send to browser
        $response = new Response(file_get_contents($filePath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="watchlist.zip"');
        $response->headers->set('Content-length', filesize($filePath));

        return $response;
    }

    /**
     * @return Response
     *
     * @Route("/_huh_watchlist/item", name="huh_watchlist_item")
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
                if (!empty($data['pid'])) {
                    $watchlist = WatchlistModel::findByUuid($data['pid']);
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

                    $result = $item->delete();


                    if ($result > 0) {
                        return new Response('Watchlist item deleted successfully.');
                    }

                    return new Response('Error deleting watchlist item.', 500);
                }

                // create mode
                // already existing?
                if (null !== $this->watchlistUtil->getWatchlistItemByData($data, $watchlist->id)) {
                    return new Response(
                        $this->translator->trans('MSC.watchlistBundle.itemAlreadyInCurrentWatchlist', [], 'contao_default'), 409);
                }

                switch ($data['type']) {
                    case WatchlistItemContainer::TYPE_FILE:
                        $fileModel = FilesModel::findByUuid($data['file']);

                        if (null === $fileModel) {
                            return new Response('File with the given uuid couldn\'t be found.', 404);
                        }

                        // get title from file
                        if (!isset($data['title'])) {
                            // filename is the fallback
                            $data['title'] = $fileModel->name;

                            // translate
                            $meta = StringUtil::deserialize($fileModel->meta, true);

                            if (isset($meta[$GLOBALS['TL_LANGUAGE']]['title'])) {
                                $data['title'] = $meta[$GLOBALS['TL_LANGUAGE']]['title'];
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
                        if (null === $this->utils->model()->findModelInstanceByPk($data['entityTable'], $data['entity'])) {
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
