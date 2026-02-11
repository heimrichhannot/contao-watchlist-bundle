<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Util;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Event\WatchlistItemDataEvent;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemFactory;
use HeimrichHannot\WatchlistBundle\Item\WatchlistItemType;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Watchlist\AuthorType;
use HeimrichHannot\WatchlistBundle\Watchlist\WatchlistContent;
use HeimrichHannot\WatchlistBundle\Watchlist\WatchlistContentFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class WatchlistUtil
{
    private ?WatchlistModel $currentWatchlist = null;
    private bool $currentWatchlistLoaded = false;

    public function __construct(
        private readonly ContaoFramework          $framework,
        private readonly Utils                    $utils,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RouterInterface          $router,
        private readonly InsertTagParser          $insertTagParser,
        private readonly WatchlistItemFactory     $watchlistItemFactory,
        private readonly TokenStorageInterface    $tokenStorage,
        private readonly RequestStack             $requestStack,
        private readonly WatchlistContentFactory  $watchlistContentFactory,
    ) {}

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

        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof UserInterface) {
            $watchlist->setUser($user);
        } else {
            $watchlist->setUser(session_id());
        }

        foreach ($data as $field => $value) {
            $watchlist->{$field} = $value;
        }

        $watchlist->save();

        return $watchlist;
    }

    public function addItemToWatchlist(array $data, WatchlistModel $watchlist): ?Model
    {
        $watchlistItem = new WatchlistItemModel();

        $time = time();

        $watchlistItem->setRow([
            'tstamp' => $time,
            'dateAdded' => $time,
            'pid' => $watchlist->id,
        ]);

        foreach ($data as $field => $value) {
            $watchlistItem->{$field} = $value;
        }

        $watchlistItem->save();

        $watchlist->tstamp = $time;
        $watchlist->save();

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

    private function returnCurrentWatchlist(?WatchlistModel $watchlistModel = null): ?WatchlistModel
    {
        $this->currentWatchlist = $watchlistModel;
        $this->currentWatchlistLoaded = true;

        return $this->currentWatchlist;
    }

    public function getCurrentWatchlist(array $options = []): ?WatchlistModel
    {
        $createIfNotExisting = $options['createIfNotExisting'] ?? false;

        if ($this->currentWatchlistLoaded) {
            if (!$createIfNotExisting || null !== $this->currentWatchlist) {
                return $this->currentWatchlist;
            }
        }

        $this->framework->getAdapter(System::class)->loadLanguageFile('default');

        $rootPage = $options['rootPage'] ?? 0;

        if (null === ($config = $this->getCurrentWatchlistConfig($rootPage))) {
            return $this->returnCurrentWatchlist();
        }

        // create search criteria

        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user && $user instanceof BackendUser) {
            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                AuthorType::USER->value,
                $user->id,
            ];
        } elseif ($user && $user instanceof FrontendUser) {
            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                AuthorType::MEMBER->value,
                $user->id,
            ];
        } else {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                return $this->returnCurrentWatchlist();
            }


            if (!$request->getSession()->isStarted()) {
                $request->getSession()->start();
                $request->getSession()->set('wl_init', true);
            }

            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                AuthorType::SESSION->value,
                $request->getSession()->getId(),
            ];
        }

        if (null !== ($watchlist = $this->utils->model()->findOneModelInstanceBy('tl_watchlist', $columns, $values))) {
            return $this->returnCurrentWatchlist($watchlist);
        }

        if (!$createIfNotExisting) {
            return $this->returnCurrentWatchlist();
        }

        return $this->returnCurrentWatchlist(
            $this->createWatchlist(
                $GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'],
                (int)$config->id
            )
        );
    }

    /**
     * @param WatchlistConfigModel $config
     *
     * @throws \Exception
     * @deprecated Use WatchlistContentFactory::build() instead
     */
    public function parseWatchlistContent(FrontendTemplate $template, string $currentUrl, int $rootPage, Model $config, ?Model $watchlist = null): string
    {
        return (string)$this->watchlistContentFactory->build(
            watchlistModel: $watchlist ?: $config,
            pageModel: PageModel::findByPk($rootPage),
            legacyTemplate: $template,
        );
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
            [$watchlist, $id]
        );
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
