<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Controller\AjaxController;
use HeimrichHannot\WatchlistBundle\Util\WatchlistUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FrontendModule(WatchlistModuleController::TYPE,category="miscellaneous").
 */
class WatchlistModuleController extends AbstractController
{
    use PageAssetsTrait;

    public const TYPE = 'watchlist';

    public function __construct(
        protected WatchlistUtil $watchlistUtil,
        private readonly Utils $utils,
    )
    {
    }

    public function getResponse(Template $template, ModuleModel $module, Request $request): Response
    {
        $this->addPageEntrypoint('contao-watchlist-bundle', [
            'TL_JAVASCRIPT' => [
                'contao-watchlist-bundle' => 'bundles/heimrichhannotwatchlistbundle/assets/contao-watchlist-bundle.js|static',
            ],
        ]);

        global $objPage;

        // watchlist
        $config = $this->watchlistUtil->getCurrentWatchlistConfig();
        $watchlist = $this->watchlistUtil->getCurrentWatchlist();

        if (!$config) {
            return $template->getResponse();
        }

        $currentUrl = parse_url((string)Environment::get('uri'), \PHP_URL_PATH);

        $template->watchlistUpdateUrl = $this->utils->url()->addQueryStringParameterToUrl(
            'wl_root_page=' . $objPage->rootId . '&wl_url=' . urlencode($currentUrl),
            Environment::get('url') . AjaxController::WATCHLIST_CONTENT_URI
        );

        if (null === $watchlist) {
            $template->title = $GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'];
        } else {
            $template->title = $watchlist->title;
        }

        $template->watchlistContent = $this->watchlistUtil->parseWatchlistContent(
            new FrontendTemplate($config->watchlistContentTemplate ?: 'watchlist_content_default'), $currentUrl, $objPage->rootId, $config, $watchlist
        );

        return $template->getResponse();

//        return $template->getResponse();
    }
}
