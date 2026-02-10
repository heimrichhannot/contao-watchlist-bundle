<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Generator;

use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class WatchlistLinkGenerator
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly WatchlistUtil $watchlistUtil,
        private readonly Utils $utils,
        private readonly Environment $twig,
    )
    {
    }

    public function generateAddFileLink(string $fileUuid, ?string $title = null, ?string $watchlistUuid = null): string
    {
        // file not existing?
        if (null === FilesModel::findByUuid($fileUuid)) {
            return '';
        }

        $postData = $this->createDefaultPostData();

        if (Validator::isBinaryUuid($fileUuid)) {
            $fileUuid = StringUtil::binToUuid($fileUuid);
        }

        $postData['type'] = WatchlistItemContainer::TYPE_FILE;
        $postData['file'] = $fileUuid;

        if ($title) {
            $postData['title'] = $title;
        }

        $watchlist = $this->watchlistUtil->getCurrentWatchlist();

        if ($watchlistUuid) {
            $postData['pid'] = $watchlistUuid;
        } elseif ($watchlist) {
            $postData['pid'] = $watchlist->uuid;
        }

        $url = $this->router->generate('huh_watchlist_item');

        $data = [
            'href' => $url,
            'isAdded' => null !== $watchlist && null !== $this->watchlistUtil->getWatchlistItemByData($postData, $watchlist->id),
            'postData' => $postData,
            'hash' => md5(implode('_', array_filter([$postData['type'] ?? [], $postData['pid'] ?? [], $postData['file'] ?? []]))),
        ];

        $config = $this->watchlistUtil->getCurrentWatchlistConfig();

        $template = '@Contao/' . ($config->insertTagAddItemTemplate ?: 'insert_tag/watchlist_add_item') . '.html.twig';

        return $this->twig->render($template, $data);
    }

    public function generateEntityLink(
        string $table,
        int $id,
        string $title,
        ?string $entityUrl = null,
        ?string $entityFile = null,
        ?string $watchlistUuid = null
    ): string
    {
        // entity not existing?
        $existing = $this->utils->model()->findModelInstanceByPk($table, $id);

        if (!$existing) {
            return '';
        }

        $postData = $this->createDefaultPostData();

        $postData['type'] = WatchlistItemContainer::TYPE_ENTITY;
        $postData['entityTable'] = $table;
        $postData['entity'] = $id;
        $postData['title'] = $title;

        if ($entityUrl) {
            $postData['entityUrl'] = $entityUrl;
        }

        if ($entityFile) {
            $postData['entityFile'] = $entityFile;
        }

        if ($watchlistUuid) {
            $postData['pid'] = $watchlistUuid;
        }

        $watchlist = $this->watchlistUtil->getCurrentWatchlist();

        $data = [
            'isAdded' => null !== $watchlist && null !== $this->watchlistUtil->getWatchlistItemByData($postData, $watchlist->id),
            'postData' => $postData,
            'hash' => md5(implode('_', [$postData['type'], $postData['pid'], $postData['entityTable'], $postData['entity']])),
        ];

        $data['href'] = $this->router->generate('huh_watchlist_item');

        $config = $this->watchlistUtil->getCurrentWatchlistConfig();

        $template = '@Contao/' . ($config->insertTagAddItemTemplate ?: 'watchlist_add_item') . '.html.twig';

        return $this->twig->render($template, $data);
    }

    protected function createDefaultPostData(): array
    {
        $postData = [];

        if ($page = $this->utils->request()->getCurrentPageModel()) {
            $page->loadDetails();
            $postData['page'] = $page->id;
            $postData['rootPage'] = $page->rootId;
        }

        if ($autoItem = Input::get('auto_item', false, true)) {
            $postData['autoItem'] = $autoItem;
        }

        return $postData;
    }
}
