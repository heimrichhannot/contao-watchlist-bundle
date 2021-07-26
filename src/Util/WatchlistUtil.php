<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Util;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
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
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class WatchlistUtil
{
    protected ContaoFramework $framework;
    protected DatabaseUtil    $DatabaseUtil;
    protected Utils           $utils;
    protected ModelUtil       $modelUtil;
    protected UrlUtil         $urlUtil;
    protected FileUtil        $fileUtil;
    protected ImageUtil       $imageUtil;

    public function __construct(
        ContaoFramework $framework,
        DatabaseUtil $databaseUtil,
        Utils $utils,
        ModelUtil $modelUtil,
        UrlUtil $urlUtil,
        FileUtil $fileUtil,
        ImageUtil $imageUtil
    ) {
        $this->framework = $framework;
        $this->databaseUtil = $databaseUtil;
        $this->utils = $utils;
        $this->modelUtil = $modelUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
        $this->imageUtil = $imageUtil;
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

        // set author
        if ($this->utils->container()->isBackend()) {
            // bind to user
            $watchlist->authorType = DcaUtil::AUTHOR_TYPE_USER;
            $watchlist->author = BackendUser::getInstance()->id;
        } else {
            if (FE_USER_LOGGED_IN) {
                // bind to member
                $watchlist->authorType = DcaUtil::AUTHOR_TYPE_MEMBER;
                $watchlist->author = FrontendUser::getInstance()->id;
            } else {
                // session
                $watchlist->authorType = DcaUtil::AUTHOR_TYPE_SESSION;
                $watchlist->author = session_id();
            }
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
        if ($this->utils->container()->isBackend()) {
            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                DcaUtil::AUTHOR_TYPE_USER,
                BackendUser::getInstance()->id,
            ];
        } else {
            if (FE_USER_LOGGED_IN) {
                $columns = [
                    'tl_watchlist.authorType=?',
                    'tl_watchlist.author=?',
                ];

                $values = [
                    DcaUtil::AUTHOR_TYPE_MEMBER,
                    FrontendUser::getInstance()->id,
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
        }

        if (null !== ($watchlist = $this->modelUtil->findOneModelInstanceBy('tl_watchlist', $columns, $values))) {
            return $watchlist;
        }

        if (!$createIfNotExisting) {
            return null;
        }

        return $this->createWatchlist($GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'], (int) $config->id);
    }

    public function parseWatchlistContent(FrontendTemplate $template, string $currentUrl, int $rootPage, Model $config, ?Model $watchlist = null): string
    {
        $template->itemUrl = Environment::get('url').AjaxController::WATCHLIST_ITEM_URI;
        $template->watchlistDownloadAllUrl = $this->urlUtil->addQueryString('wl_root_page='.$rootPage,
            Environment::get('url').AjaxController::WATCHLIST_DOWNLOAD_ALL_URI);

        if ($config->addShare && null !== ($sharePage = $this->modelUtil->findModelInstanceByPk('tl_page', $config->shareJumpTo))) {
            $template->watchlistShareUrl = $this->urlUtil->addQueryString('watchlist='.$watchlist->uuid, $sharePage->getFrontendUrl());
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

                            $template->hasDownloadableFiles = true;

                            // create the url with file-GET-parameter so that also nonpublic files can be accessed safely
                            $url = $this->framework->getAdapter(Controller::class)->replaceInsertTags('{{download_link::'.$file->path.'}}');
                            $query = parse_url($url, PHP_URL_QUERY);
                            $url = $this->urlUtil->addQueryString($query, $currentUrl);

                            $cleanedItem['downloadUrl'] = $this->urlUtil->removeQueryString(['wl_root_page', 'wl_url'], $url);

                            // add image if file is such
                            if (\in_array($file->extension, explode(',', Config::get('validImageTypes')))) {
                                // Override the default image size
                                if ($config->imgSize) {
                                    $imgSize = StringUtil::deserialize($config->imgSize, true);

                                    if ($imgSize[0] > 0 || $imgSize[1] > 0 || is_numeric($imgSize[2])) {
                                        $cleanedItem['size'] = $config->imgSize;
                                    }
                                }

                                // force lightbox support
                                $cleanedItem['fullsize'] = true;

                                $this->imageUtil->addToTemplateData('file', '',
                                    $cleanedItem, $cleanedItem, null, $watchlist->uuid);
                            }
                        } else {
                            $cleanedItem['existing'] = false;
                        }

                        $hash = md5(implode('_', [$cleanedItem['type'], $cleanedItem['pid'], $cleanedItem['file']]));

                        break;

                    case WatchlistItemContainer::TYPE_ENTITY:
                        $cleanedItem['entityTable'] = $item['entityTable'];
                        $cleanedItem['entity'] = $item['entity'];
                        $cleanedItem['entityUrl'] = $item['entityUrl'];

                        $existing = $this->databaseUtil->findResultByPk($cleanedItem['entityTable'], $cleanedItem['entity']);

                        $cleanedItem['existing'] = $existing->numRows > 0;

                        $hash = md5(implode('_', [$cleanedItem['type'], $cleanedItem['pid'], $cleanedItem['entityTable'], $cleanedItem['entity']]));

                        break;
                }

                $cleanedItem['postData'] = htmlspecialchars(json_encode($cleanedItem), ENT_QUOTES, 'UTF-8');

                $cleanedItem['hash'] = $hash;

                $items[] = $cleanedItem;
            }

            $template->items = $items;
        }

        return $template->parse();
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
        $modelOptions = $options['modelOptions'] ?? null;

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
}
