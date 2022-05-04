<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\System;
use Contao\Template;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * FrontendModule(WatchlistModuleController::TYPE,category="miscellaneous").
 */
class WatchlistModuleController
{
    const TYPE = 'watchlist';

    /** @var DatabaseUtil */
    protected $databaseUtil;
    /** @var WatchlistUtil */
    protected $watchlistUtil;
    /** @var UrlUtil */
    protected $urlUtil;
    /** @var FileUtil */
    protected $fileUtil;
    /** @var SessionInterface */
    protected $session;

    public function __construct(DatabaseUtil $databaseUtil, WatchlistUtil $watchlistUtil, UrlUtil $urlUtil, FileUtil $fileUtil, SessionInterface $session)
    {
        $this->databaseUtil = $databaseUtil;
        $this->watchlistUtil = $watchlistUtil;
        $this->urlUtil = $urlUtil;
        $this->fileUtil = $fileUtil;
        $this->session = $session;
    }

    public function getResponse(Template $template, ModuleModel $module, Request $request): ?Response
    {
        // load js assets
        if (System::getContainer()->has('HeimrichHannot\EncoreBundle\Asset\FrontendAsset')) {
            System::getContainer()->get(FrontendAsset::class)->addActiveEntrypoint('contao-watchlist-bundle');
        } else {
            $GLOBALS['TL_JAVASCRIPT']['contao-watchlist-bundle'] = 'bundles/heimrichhannotwatchlistbundle/assets/contao-watchlist-bundle.js|static';
        }

        global $objPage;

        // watchlist
        $config = $this->watchlistUtil->getCurrentWatchlistConfig();
        $watchlist = $this->watchlistUtil->getCurrentWatchlist();

        if (!$config) {
            return null;
        }

        $currentUrl = parse_url(Environment::get('uri'), \PHP_URL_PATH);

        $template->watchlistUpdateUrl = $this->urlUtil->addQueryString('wl_root_page='.$objPage->rootId.'&wl_url='.urlencode($currentUrl),
            Environment::get('url').AjaxController::WATCHLIST_CONTENT_URI);

        if (null === $watchlist) {
            $template->title = $GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'];
        } else {
            $template->title = $watchlist->title;
        }

        $template->watchlistContent = $this->watchlistUtil->parseWatchlistContent(
            new FrontendTemplate($config->watchlistContentTemplate ?: 'watchlist_content_default'), $currentUrl, $objPage->rootId, $config, $watchlist
        );

        return null;

//        return $template->getResponse();
    }
}
