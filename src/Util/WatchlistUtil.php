<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Util;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Database\Result;
use Contao\Environment;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Image;
use Contao\Model;
use Contao\Model\Collection;
use Contao\PageModel;
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
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemFactory;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemType;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class WatchlistUtil
{

    /**
     * @var Security
     */
    private Security $security;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Utils $utils,
        Security $security,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RouterInterface $router,
        private readonly InsertTagParser $insertTagParser,
        private readonly WatchlistItemFactory $watchlistItemFactory,
    )
    {
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
            'uuid' => md5(uniqid(random_int(0, mt_getrandmax()), true)),
            'config' => $config,
        ]);

        // avoid having duplicate uuids
        while (null !== $this->utils->model()->findOneModelInstanceBy('tl_watchlist', ['tl_watchlist.uuid=?'], [$watchlist->uuid])) {
            $watchlist->uuid = md5(uniqid(random_int(0, mt_getrandmax()), true));
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

                return WatchlistItemModel::findOneBy(
                    ['tl_watchlist_item.type=?', 'tl_watchlist_item.pid=?', 'tl_watchlist_item.file=UNHEX(?)'],
                    [WatchlistItemContainer::TYPE_FILE, $watchlist, bin2hex((string)$itemData['file'])]
                );

            case WatchlistItemContainer::TYPE_ENTITY:
                return WatchlistItemModel::findOneBy(
                    ['tl_watchlist_item.type=?', 'tl_watchlist_item.pid=?', 'tl_watchlist_item.entityTable=?', 'tl_watchlist_item.entity=?'],
                    [WatchlistItemContainer::TYPE_ENTITY, $watchlist, $itemData['entityTable'], $itemData['entity']]
                );
        }

        return null;
    }

    /**
     * @return WatchlistModel|null
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

        if (null !== ($watchlist = $this->utils->model()->findOneModelInstanceBy('tl_watchlist', $columns, $values))) {
            return $watchlist;
        }

        if (!$createIfNotExisting) {
            return null;
        }

        return $this->createWatchlist($GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'], (int)$config->id);
    }

    /**
     * @param WatchlistConfigModel $config
     *
     * @throws \Exception
     */
    public function parseWatchlistContent(FrontendTemplate $template, string $currentUrl, int $rootPage, Model $config, ?Model $watchlist = null): string
    {
        $template->watchlistUrl = $this->utils->url()->addQueryStringParameterToUrl('wl_root_page=' . $rootPage, Environment::get('url') . AjaxController::WATCHLIST_URI);
        $template->itemUrl = Environment::get('url') . AjaxController::WATCHLIST_ITEM_URI;
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

            $watchlistItemModels = $this->getWatchlistItems($watchlist->id, [
                'modelOptions' => ['order' => 'dateAdded DESC'],
            ]);
            foreach ($watchlistItemModels as $model) {
                $item = $model->row();

                // clean items for frontend (don't pass internal info to outside for security reasons)
                $cleanedItem = [
                    'rootPage' => $rootPage,
                    'type' => $item['type'],
                    'title' => $item['title'],
                ];

                $wlItem = $this->watchlistItemFactory->build($model);
                $cleanedItem = $wlItem->applyToTemplateData($cleanedItem);

                if ($wlItem->getType() === WatchlistItemType::ENTITY && $wlItem->fileExist()) {
                    $template->hasDownloadableFiles = true;
                }

                $cleanedItem['postData'] = htmlspecialchars(
                    json_encode(array_filter($cleanedItem, 'is_scalar')),
                    \ENT_QUOTES,
                    'UTF-8'
                );

                $event = $this->eventDispatcher->dispatch(
                    new WatchlistItemDataEvent($cleanedItem, $config),
                    WatchlistItemDataEvent::class
                );

                $items[] = $event->getItem();
            }

            $template->items = $items;
        }

        return $this->insertTagParser->replace($template->parse());
    }

    public function getCurrentWatchlistConfig(int $rootPage = 0): ?WatchlistConfigModel
    {
        if (!$rootPage) {
            global $objPage;

            $rootPage = $objPage->rootId;
        }

        $rootPage = PageModel::findByPk($rootPage);

        if (!$rootPage) {
            return null;
        }

        return WatchlistConfigModel::findByPk($rootPage->watchlistConfig);
    }

    /**
     * Get watchlist items from watchlist id.
     *
     * @param int $watchlistId The watchlist ist. If not set, the watchlist is automatically loaded
     * @param array $options Additional options. Options: modelOptions
     * @return WatchlistItemModel[] The watchlist items collection
     */
    public function getWatchlistItems(int $watchlistId = 0, array $options = []): array
    {
        if (0 === $watchlistId) {
            $watchlistModel = $this->getCurrentWatchlist();

            if ($watchlistModel) {
                $watchlistId = (int)$watchlistModel->id;
            }
        }

        $modelOptions = $options['modelOptions'] ?? [];

        return WatchlistItemModel::findBy(
            ['tl_watchlist_item.pid=?'],
            [$watchlistId],
            $modelOptions
        )?->getModels() ?? [];
    }

    public function getWatchlistItem(int $id, int $watchlist): ?Model
    {
        return WatchlistItemModel::findOneBy(
            ['tl_watchlist_item.pid=?', 'tl_watchlist_item.id=?'],
            [$watchlist, $id]);
    }

    public function getWatchlistShareUrl(?Model $watchlist = null, ?Model $config = null): string
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

        $sharePage = $this->utils->model()->findModelInstanceByPk('tl_page', $config->shareJumpTo);

        if (!$sharePage) {
            return '';
        }

        return $this->utils->url()->addQueryStringParameterToUrl(
            'watchlist=' . $watchlist->uuid,
            Environment::get('url') . '/' . $sharePage->getFrontendUrl()
        );
    }
}
