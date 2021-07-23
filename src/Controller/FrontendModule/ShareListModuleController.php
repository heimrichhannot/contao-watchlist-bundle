<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Environment;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(ShareListModuleController::TYPE,category="miscellaneous")
 */
class ShareListModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'watchlist_share_list';
    protected DatabaseUtil     $databaseUtil;
    protected WatchlistUtil    $watchlistUtil;
    protected UrlUtil          $urlUtil;
    protected FileUtil         $fileUtil;

    public function __construct(DatabaseUtil $databaseUtil, WatchlistUtil $watchlistUtil, UrlUtil $urlUtil, FileUtil $fileUtil)
    {
        $this->databaseUtil = $databaseUtil;
        $this->watchlistUtil = $watchlistUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        if (!($watchlistUuid = $request->get('watchlist'))) {
            $template->watchlistNotFound = true;

            return $template->getResponse();
        }

        $watchlist = $this->databaseUtil->findOneResultBy('tl_watchlist', [
            'tl_watchlist.uuid=?',
        ], [
            $watchlistUuid,
        ]);

        if ($watchlist->numRows < 1) {
            $template->watchlistNotFound = true;

            return $template->getResponse();
        }

        $currentUrl = parse_url(Environment::get('uri'), PHP_URL_PATH);

        $items = [];

        foreach ($this->watchlistUtil->getWatchlistItems($watchlist->id, [
            'modelOptions' => ['order' => 'title ASC'],
        ]) as $item) {
            $item['watchlistConfig'] = $watchlist->config;

            switch ($item['type']) {
                case WatchlistItemContainer::TYPE_FILE:
                    $item['file'] = StringUtil::binToUuid($item['file']);

                    $path = $this->fileUtil->getPathFromUuid($item['file']);

                    if ($path) {
                        $item['existing'] = true;

                        $template->hasDownloadableFiles = true;

                        $item['downloadUrl'] = $this->urlUtil->addQueryString('file='.$path, urldecode($currentUrl));
                    } else {
                        $item['existing'] = false;
                    }

                    break;

                case WatchlistItemContainer::TYPE_ENTITY:
                    $existing = $this->databaseUtil->findResultByPk($item['entityTable'], $item['entity']);

                    $item['existing'] = $existing->numRows > 0;

                    break;
            }

            $items[] = $item;
        }

        $template->items = $items;

        return $template->getResponse();
    }
}
