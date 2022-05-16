<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Util;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Event\WatchlistItemDataEvent;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class WatchlistUtil
{
    /** @var ContaoFramework */
    protected $framework;
    /** @var DatabaseUtil */
    protected $DatabaseUtil;
    /** @var Utils */
    protected $utils;
    /** @var ModelUtil */
    protected $modelUtil;
    /** @var UrlUtil */
    protected $urlUtil;
    /** @var FileUtil */
    protected $fileUtil;
    /** @var ImageUtil */
    protected $imageUtil;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    private RouterInterface $router;

    public function __construct(
        ContaoFramework $framework,
        DatabaseUtil $databaseUtil,
        Utils $utils,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        FileUtil $fileUtil,
        ImageUtil $imageUtil,
        Security $security,
        EventDispatcherInterface $eventDispatcher,
        RouterInterface $router
    ) {
        $this->framework = $framework;
        $this->databaseUtil = $databaseUtil;
        $this->utils = $utils;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
        $this->imageUtil = $imageUtil;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    public function createWatchlist(string $title, int $config, array $options = []): ?Model
    {
        $data = $options['data'] ?? [];

        $watchlist = new WatchlistModel();

        $time = time();

        $watchlist->setRow([
            'tstamp' => $time,
            'dateAdded' => $time,
            'title' => $title,
            'uuid' => md5(uniqid(rand(), true)),
            'config' => $config,
        ]);

        // avoid having duplicate uuids
        while (null !== $this->modelUtil->findOneModelInstanceBy('tl_watchlist', ['tl_watchlist.uuid=?'], [$watchlist->uuid])) {
            $watchlist->uuid = md5(uniqid(rand(), true));
        }

        $user = $this->security->getUser();

        if ($user && $user instanceof BackendUser) {
            $watchlist->authorType = DcaUtil::AUTHOR_TYPE_USER;
            $watchlist->author = $user->id;
        } elseif ($user && $user instanceof FrontendUser) {
            $watchlist->authorType = DcaUtil::AUTHOR_TYPE_MEMBER;
            $watchlist->author = $user->id;
        } else {
            $watchlist->authorType = DcaUtil::AUTHOR_TYPE_SESSION;
            $watchlist->author = session_id();
        }

        foreach ($data as $field => $value) {
            $watchlist->{$field} = $value;
        }

        $watchlist->save();

        return $watchlist;
    }

    public function addItemToWatchlist(array $data, int $watchlist): ?Model
    {
        $watchlistItem = new WatchlistItemModel();

        $time = time();

        $watchlistItem->setRow([
            'tstamp' => $time,
            'dateAdded' => $time,
            'pid' => $watchlist,
        ]);

        foreach ($data as $field => $value) {
            $watchlistItem->{$field} = $value;
        }

        $watchlistItem->save();

        return $watchlistItem;
    }

    public function getWatchlistItemByData(array $itemData, int $watchlist): ?Model
    {
        switch ($itemData['type']) {
            case WatchlistItemContainer::TYPE_FILE:
                if (!Validator::isBinaryUuid($itemData['file'])) {
                    $itemData['file'] = StringUtil::uuidToBin($itemData['file']);
                }

                $existingItem = $this->databaseUtil->findOneResultBy('tl_watchlist_item',
                    ['tl_watchlist_item.type=?', 'tl_watchlist_item.pid=?', 'tl_watchlist_item.file=UNHEX(?)'],
                    [WatchlistItemContainer::TYPE_FILE, $watchlist, bin2hex($itemData['file'])]
                );

                return $existingItem->numRows > 0 ? $this->modelUtil->findModelInstanceByPk('tl_watchlist_item', $existingItem->id) : null;

            case WatchlistItemContainer::TYPE_ENTITY:
                $existingItem = $this->databaseUtil->findOneResultBy('tl_watchlist_item',
                    ['tl_watchlist_item.type=?', 'tl_watchlist_item.pid=?', 'tl_watchlist_item.entityTable=?', 'tl_watchlist_item.entity=?'],
                    [WatchlistItemContainer::TYPE_ENTITY, $watchlist, $itemData['entityTable'], $itemData['entity']]
                );

                return $existingItem->numRows > 0 ? $this->modelUtil->findModelInstanceByPk('tl_watchlist_item', $existingItem->id) : null;
        }

        return null;
    }

    /**
     * @return Model
     */
    public function getCurrentWatchlist(array $options = []): ?Model
    {
        $this->framework->getAdapter(System::class)->loadLanguageFile('default');

        $createIfNotExisting = $options['createIfNotExisting'] ?? false;
        $rootPage = $options['rootPage'] ?? 0;

        if (null === ($config = $this->getCurrentWatchlistConfig($rootPage))) {
            return null;
        }

        // create search criteria

        $user = $this->security->getUser();

        if ($user && $user instanceof BackendUser) {
            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                DcaUtil::AUTHOR_TYPE_USER,
                $user->id,
            ];
        } elseif ($user && $user instanceof FrontendUser) {
            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                DcaUtil::AUTHOR_TYPE_MEMBER,
                $user->id,
            ];
        } else {
            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                DcaUtil::AUTHOR_TYPE_SESSION,
                session_id(),
            ];
        }

        if (null !== ($watchlist = $this->modelUtil->findOneModelInstanceBy('tl_watchlist', $columns, $values))) {
            return $watchlist;
        }

        if (!$createIfNotExisting) {
            return null;
        }

        return $this->createWatchlist($GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'], (int) $config->id);
    }

    /**
     * @param WatchlistConfigModel $config
     *
     * @throws \Exception
     */
    public function parseWatchlistContent(FrontendTemplate $template, string $currentUrl, int $rootPage, Model $config, ?Model $watchlist = null): string
    {
        $template->watchlistUrl = $this->urlUtil->addQueryString('wl_root_page='.$rootPage, Environment::get('url').AjaxController::WATCHLIST_URI);
        $template->itemUrl = Environment::get('url').AjaxController::WATCHLIST_ITEM_URI;
        $template->watchlistDownloadAllUrl = $this->router->generate('huh_watchlist_downlad_all', ['wl_root_page' => $rootPage]);

        if ($watchlist && $config->addShare) {
            $template->watchlistShareUrl = $this->getWatchlistShareUrl($watchlist, $config);
        }

        $template->config = $config;

        // items
        if (null === $watchlist) {
            $template->items = [];
        } else {
            $items = [];

            foreach ($this->getWatchlistItems($watchlist->id, [
                'modelOptions' => ['order' => 'dateAdded DESC'],
            ]) as $item) {
                // clean items for frontend (don't pass internal info to outside for security reasons)
                $cleanedItem = [
                    'rootPage' => $rootPage,
                    'type' => $item['type'],
                    'title' => $item['title'],
                ];

                switch ($item['type']) {
                    case WatchlistItemContainer::TYPE_FILE:
                        $cleanedItem['file'] = StringUtil::binToUuid($item['file']);

                        $file = $this->fileUtil->getFileFromUuid($item['file']);

                        if ($file->path) {
                            $cleanedItem['existing'] = true;

                            $cleanedItem['postData'] = htmlspecialchars(json_encode($cleanedItem), \ENT_QUOTES, 'UTF-8');

                            $template->hasDownloadableFiles = true;

                            // create the url with file-GET-parameter so that also nonpublic files can be accessed safely
                            $url = $this->framework->getAdapter(Controller::class)->replaceInsertTags('{{download_link::'.$file->path.'}}');
                            $query = parse_url($url, \PHP_URL_QUERY);
                            $url = $this->urlUtil->addQueryString($query, $currentUrl);

                            $cleanedItem['downloadUrl'] = $this->urlUtil->removeQueryString(['wl_root_page', 'wl_url'], $url);

                            // add image if file is such
                            $this->addImageToItemData($cleanedItem, 'file', $file, $config, $watchlist);
                        } else {
                            $cleanedItem['existing'] = false;

                            $cleanedItem['postData'] = htmlspecialchars(json_encode($cleanedItem), \ENT_QUOTES, 'UTF-8');
                        }

                        $hash = md5(implode('_', [$cleanedItem['type'], $cleanedItem['pid'], $cleanedItem['file']]));

                        break;

                    case WatchlistItemContainer::TYPE_ENTITY:
                        $cleanedItem['entityTable'] = $item['entityTable'];
                        $cleanedItem['entity'] = $item['entity'];
                        $cleanedItem['entityUrl'] = $item['entityUrl'];
                        $cleanedItem['entityFile'] = $item['entityFile'] ? StringUtil::binToUuid($item['entityFile']) : '';

                        $existing = $this->databaseUtil->findResultByPk($cleanedItem['entityTable'], $cleanedItem['entity']);

                        $cleanedItem['existing'] = $existing->numRows > 0;

                        $hash = md5(implode('_', [$cleanedItem['type'], $cleanedItem['pid'], $cleanedItem['entityTable'], $cleanedItem['entity']]));

                        $cleanedItem['postData'] = htmlspecialchars(json_encode($cleanedItem), \ENT_QUOTES, 'UTF-8');

                        $file = $this->fileUtil->getFileFromUuid($item['entityFile']);

                        if ($file->path) {
                            // add image if file is such
                            $this->addImageToItemData($cleanedItem, 'entityFile', $file, $config, $watchlist);
                        }

                        break;
                }

                $cleanedItem['hash'] = $hash;

                $event = $this->eventDispatcher->dispatch(
                    new WatchlistItemDataEvent($cleanedItem, $config),
                    WatchlistItemDataEvent::class
                );

                $items[] = $event->getItem();
            }

            $template->items = $items;
        }

        return $template->parse();
    }

    public function addImageToItemData(array &$item, string $field, File $file, Model $config, Model $watchlist)
    {
        if (!\in_array($file->extension, explode(',', Config::get('validImageTypes')))) {
            return;
        }

        // Override the default image size
        if ($config->imgSize) {
            $imgSize = StringUtil::deserialize($config->imgSize, true);

            if ($imgSize[0] > 0 || $imgSize[1] > 0 || is_numeric($imgSize[2])) {
                $item['size'] = $config->imgSize;
            }
        }

        // force lightbox support
        $item['fullsize'] = true;

        $item['imageData_'.$field] = $this->imageUtil->prepareImage($item, [
            'imageField' => $field,
            'imageSelectorField' => null,
            'lightboxId' => $watchlist->uuid,
        ]);
    }

    public function getCurrentWatchlistConfig(int $rootPage = 0): ?Model
    {
        if (!$rootPage) {
            global $objPage;

            $rootPage = $objPage->rootId;
        }

        if (null === ($page = $this->databaseUtil->findResultByPk('tl_page', $rootPage)) || $page->numRows < 1) {
            return null;
        }

        return $this->modelUtil->findModelInstanceByPk('tl_watchlist_config', $page->watchlistConfig);
    }

    public function getWatchlistItems(int $watchlist, array $options = []): array
    {
        $modelOptions = $options['modelOptions'] ?? [];

        if (null === ($items = $this->modelUtil->findModelInstancesBy('tl_watchlist_item', ['tl_watchlist_item.pid=?'], [$watchlist], $modelOptions))) {
            return [];
        }

        return $items->fetchAll();
    }

    public function getWatchlistItem(int $id, int $watchlist): ?Model
    {
        if (null === ($item = $this->databaseUtil->findOneResultBy('tl_watchlist_item',
                ['tl_watchlist_item.pid=?', 'tl_watchlist_item.id=?'], [$watchlist, $id])) || $item->numRows < 1) {
            return null;
        }

        return $item;
    }

    public function getWatchlistShareUrl(Model $watchlist = null, Model $config = null): string
    {
        if (null === $watchlist) {
            $watchlist = $this->getCurrentWatchlist();
        }

        if (null === $config) {
            $config = $this->getCurrentWatchlistConfig();
        }

        if (!$watchlist || !$config) {
            return '';
        }

        $sharePage = $this->modelUtil->findModelInstanceByPk('tl_page', $config->shareJumpTo);

        if (!$sharePage) {
            return '';
        }

        return $this->urlUtil->addQueryString('watchlist='.$watchlist->uuid, Environment::get('url').'/'.$sharePage->getFrontendUrl());
    }
}
